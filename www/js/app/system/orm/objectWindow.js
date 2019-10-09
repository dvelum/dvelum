/**
 * Edit window for ORM object
 *
 * @event dataSaved
 * @event showdictionarywin
 * @event fieldRemoved
 * @event indexRemoved
 *
 */
Ext.define('app.crud.orm.ObjectWindow', {
	extend: 'Ext.window.Window',
	objectName:null,
	dataGrid:null,
	indexGrid:null,
	indexStore:null,
	dataStore:null,
	configForm:null,
	tabPanel:null,
	objectList:null,
	isExternal:false,
	isSystem:false,
	maximizable:true,
    sharding:false,
	constructor: function(config) {
		config = Ext.apply({
			modal: false,
			layout:'fit',
			width: app.checkWidth(700),
			height:app.checkHeight(600),
			closeAction: 'destroy'
		}, config || {});

		this.callParent(arguments);
	},

    initComponent:function(){
		this.dataStore = Ext.create('Ext.data.Store', {
			model: 'app.crud.orm.Field',
			proxy: {
				type: 'ajax',
				url:app.crud.orm.Actions.listObjFields,
				reader: {
					type: 'json',
					rootProperty: 'data',
					idProperty: 'name'
				},
				extraParams:{'object':this.objectName},
				actionMethods : {
					create : 'POST',
					read   : 'POST',
					update : 'POST',
					destroy: 'POST'
				},
				simpleSortMode: false
			},
			autoLoad: false,
			sorters: [{
				property:'system',
				direction:'DESC'
			},{
				property : 'name',
				direction: 'ASC'
			}]
		});

		this.indexStore = Ext.create('Ext.data.Store', {
			model: 'app.crud.orm.Index',
			remoteSort:false,
			proxy: {
				type: 'ajax',
				url:app.crud.orm.Actions.listObjIndexes,
				reader:{
					type: 'json',
					rootProperty: 'data',
					idProperty: 'name'
				},
				extraParams:{'object':this.objectName},
				actionMethods : {
					create : 'POST',
					read   : 'POST',
					update : 'POST',
					destroy: 'POST'
				},
				simpleSortMode: true
			},
			autoLoad: false,
			sorters: [{
				property : 'name',
				direction: 'ASC'
			}]
		});

		this.searchField = Ext.create('SearchPanel',{
			store:this.dataStore,
			fieldNames:['name' , 'title'],
			local:true
		});

		var fieldsTbar = [];

		if(app.crud.orm.canEdit){
			fieldsTbar.push(
				{
					text:appLang.ADD_FIELD,
					scope:this,
					handler:function(){
						this.showEditField(0);
					}
				}
			);
		}

		var me = this;
		var encryptButton = Ext.create('Ext.Button',{
			hidden:true,
			icon: app.wwwRoot + 'i/system/plock.png',
			text:appLang.ENCRYPT_DATA,
			tooltip:appLang.ENCRYPT_DATA,
			handler:function(){
				this.encryptData(this.objectName);
			},
			scope:this
		});

		var decryptButton = Ext.create('Ext.Button',{
			hidden:true,
			icon: app.wwwRoot + 'i/system/unlock.png',
			text:appLang.DECRYPT_DATA,
			tooltip:appLang.DECRYPT_DATA,
			handler:function(){
				this.decryptData(this.objectName);
			},
			scope:this
		});

		fieldsTbar.push('-');
		fieldsTbar.push(encryptButton);
		fieldsTbar.push(decryptButton);
		fieldsTbar.push('->');
		fieldsTbar.push(this.searchField);

		var titleRenderer = function(value, metaData, record, rowIndex, colIndex, store){
			if(record.get('broken'))
			{
				metaData.style ='background-color:red;';
				value = '<img src="'+app.wwwRoot+'i/system/broken.png" title="'+appLang.BROKEN_LINK+'">&nbsp; ' + value;
			}
			return value;
		};

		this.dataGrid = Ext.create('Ext.grid.Panel',{
			store: this.dataStore,
			frame: false,
			loadMask:true,
			columnLines: true,
			scrollable:true,
			bodyBorder:false,
			border:false,
			title:appLang.FIELDS,
			tbar:fieldsTbar,
			encryptButton:encryptButton,
			decryptButton:decryptButton,
			defaults:{
				sortable:true
			},
			viewConfig:{
				stripeRows:true,
				enableTextSelection: true
			},
			forceFit:true,
			columns: [{
				width:50,
				align:'center',
				dataIndex: 'name',
				colid:'editcolumn',
				renderer:function(value, metaData, record, rowIndex, colIndex, store){

					if(record.get('system')) {
						return '<img src="'+app.wwwRoot+'i/system/locked.png" title="'+appLang.SYSTEM_PROTECTED_FIELD+'">';
					}

					if(!record.get('system') && app.crud.orm.canEdit){
						return '<img src="'+app.wwwRoot+'i/system/edit.png" title="'+appLang.EDIT_FIELD+'" style="cursor:pointer;">';
					}
					return '';
				}
			},{
				text:appLang.TITLE,
				dataIndex:'title',
				align:'left',
				flex:1,
				width:100,
				renderer:titleRenderer
			},{
				text: appLang.FIELD_NAME,
				dataIndex: 'name',
				width:120,
				align:'left'
			},{
				text:appLang.TYPE,
				dataIndex:'type',
				align:'center',
				width:80,
				colid:'link_type',
				scope:this,
				renderer:function(value, metaData, record, rowIndex, colIndex, store){
					if(record.get('link_type') == 'dictionary'){
						metaData.style = "background-color:#FFFDE4;cursor:pointer;height: 25px;";
						value = 'dictionary '+value;
					}
					return value;
				}
			},{
				text:appLang.REQUIRED,
				dataIndex:'required',
				align:'center',
				width:60,
				renderer:app.checkboxRenderer
			},{
				text:'NULL',
				dataIndex:'db_isNull',
				align:'center',
				width:60,
				renderer:app.checkboxRenderer
			},{
				text:appLang.UNIQUE,
				dataIndex:'unique',
				align:'center',
				width:60,
				renderer:app.checkboxRenderer
			},{
				text:appLang.IS_SEARCH,
				dataIndex:'is_search',
				align:'center',
				width:60,
				renderer:app.checkboxRenderer
			},{
				width:40,
				align:'center',
				dataIndex: 'name',
				colid:'deleteindex',
				renderer:function(value, metaData, record, rowIndex, colIndex, store){
					if(app.crud.orm.canDelete && !record.get('system')){
						return '<img src="'+app.wwwRoot+'i/system/delete.png" title="'+appLang.DELETE_FIELD+'" style="cursor:pointer;">';
					}
					return '';
				}
			}
			]
		});

		this.dataStore.on('load',function(store,records){
			var hasEncrypted = false;
			store.each(function(record){
				if(record.get('type') == 'encrypted'){
					hasEncrypted = true;
				}
			});
			if(hasEncrypted){
				this.dataGrid.decryptButton.show();
				this.dataGrid.encryptButton.show();
			}else{
				this.dataGrid.encryptButton.hide();
				this.dataGrid.decryptButton.hide();
			}
		},this);

		this.indexGrid = Ext.create('Ext.grid.Panel',{
			store: this.indexStore,
			frame: false,
			loadMask:true,
			columnLines: true,
			scrollable:true,
			bodyBorder:false,
			border:false,
			title:appLang.INDEXES,
			defaults:{
				sortable:true
			},
			viewConfig:{
				stripeRows:true,
				enableTextSelection: true
			},
			forceFit:true,
			columns: [{
				width:40,
				align:'center',
				dataIndex: 'name',
				colid:'editcolumn',
				renderer:function(value, metaData, record, rowIndex, colIndex, store){

					if(record.get('primary') || record.get('system')){
						return '<img src="'+app.wwwRoot+'i/system/locked.png" title="'+appLang.SYSTEM_PROTECTED_INDEX+'">';
					}

					if(app.crud.orm.canEdit){
						return '<img src="'+app.wwwRoot+'i/system/edit.png" title="'+appLang.EDIT_INDEX+'" style="cursor:pointer;">';
					}
					return '';
				}
			},{
				text: appLang.NAME,
				dataIndex: 'name',
				width:150,
				flex:1,
				align:'left'
			},{
				text:appLang.FULLTEXT,
				dataIndex:'fulltext',
				align:'center',
				width:70,
				renderer:app.checkboxRenderer
			},{
				text:appLang.UNIQUE,
				dataIndex:'unique',
				align:'center',
				width:70,
				renderer:app.checkboxRenderer
			},{
				text:appLang.COLUMNS,
				dataIndex:'columns',
				align:'center',
				width:200
			},{
				width:40,
				align:'center',
				dataIndex: 'name',
				colid:'deleteindex',
				renderer:function(value, metaData, record, rowIndex, colIndex, store){
					if(app.crud.orm.canDelete && !record.get('primary')){
						return '<img src="'+app.wwwRoot+'i/system/delete.png" title="'+appLang.EDIT_INDEX+'" style="cursor:pointer;">';
					}
					return '';
				}
			}
			]
		});

		if(app.crud.orm.canEdit){
			var mainConfButtons =[{
				xtype:'button',
				text:appLang.SAVE,
				width:100,
				handler:this.saveMainCfg,
				scope:this
			}];

			this.dataGrid.on('itemdblclick',function(view , record , number , event , options){
				if(record.get('system'))
					return;
				this.showEditField(record.get('name'));
			},this);


			this.indexGrid.on('itemdblclick',function(view , record , number , event , options){
				if(record.get('primary'))
					return;
				this.showEditIndex(record.get('name'));
			},this);

			this.indexGrid.addDocked({
				dock:'top',
				xtype:'toolbar',
				items:[{
					text:appLang.ADD_INDEX,
					xtype:'button',
					scope:this,
					handler:function(){
						this.showEditIndex(0);
					}
				}]
			});

		}else{
			var mainConfButtons = [];
		}

        this.acceptedDistributedFields = Ext.create('Ext.data.Store', {
            fields:[
                {name:'field', type:'string'}
            ],
            proxy: {
                type: 'ajax',
                url:app.crud.orm.Actions.acceptedDistFields,
                reader: {
                    type: 'json',
                    rootProperty: 'data',
                    idProperty: 'id'
                },
                extraParams:{'object':this.objectName},
                actionMethods : {
                    create : 'POST',
                    read   : 'POST',
                    update : 'POST',
                    destroy: 'POST'
                },
                simpleSortMode: false
            },
            autoLoad: false,
            sorters: [{
                property : 'field',
                direction: 'ASC'
            }]
        });

        this.addDistIndexCombo = Ext.create('Ext.form.field.ComboBox',{
            store:this.acceptedDistributedFields,
            displayField:'name',
            valueField:'name',
            forceSelection:true,
            queryMode: 'remote',
            queryCaching:false,
            triggers:{
                addDistIndex:{
                    cls:'dv-add-trigger',
                    handler:function(){
                        var value =  me.addDistIndexCombo.getValue();

                        if(!value || !value.length){
                            return;
                        }
                        var store =  me.distributedIndexGrid.getStore();
                        var index = store.findExact('field', value);

                        if(index==-1){
                            me.addDistributedIndex(value);
                        }
                    },
                    scope:this
                }
            }
        });

        if(this.sharding)
        {
            this.distributedIndexStore = Ext.create('Ext.data.Store', {
                model: 'app.crud.orm.ditributedIndex',
                remoteSort:false,
                proxy: {
                    type: 'ajax',
                    url:app.crud.orm.Actions.listObjDistIndexes,
                    reader:{
                        type: 'json',
                        rootProperty: 'data',
                        idProperty: 'field'
                    },
                    extraParams:{'object':this.objectName},
                    actionMethods : {
                        create : 'POST',
                        read   : 'POST',
                        update : 'POST',
                        destroy: 'POST'
                    },
                    simpleSortMode: true
                },
                autoLoad: false,
                sorters: [{
                    property : 'field',
                    direction: 'ASC'
                }]
            });

            this.distributedIndexGrid = Ext.create('Ext.grid.Panel',{
                store:this.distributedIndexStore,
                frame: false,
                loadMask:true,
                columnLines: true,
                scrollable:true,
                bodyBorder:false,
                border:false,
                title:appLang.DISTRIBUTED_INDEXES,
                tbar:[
                    appLang.ADD_INDEX + ':',
                    this.addDistIndexCombo
                ],
                defaults:{
                    sortable:true
                },
                viewConfig:{
                    stripeRows:true,
                    enableTextSelection: true
                },
                forceFit:true,
                columns: [{
                    text: appLang.FIELD,
                    dataIndex: 'field',
                    width:150,
                    flex:1,
                    align:'left'
                },{
                    xtype:'actioncolumn',
                    align:'center',
                    width:25,
                    items:[
                        {
                            icon:app.wwwRoot+'i/system/delete.png',
                            handler:function(view, row, col, item, e, record){
                                me.deleteDistributedIndex(record.get('field'));
                            },
                            isDisabled:function(view,row,col,item,record){
                                return record.get('is_system');
                            }
                        }
                    ]
                }]
            });
        }

		if(app.crud.orm.canEdit || app.crud.orm.canDelete){
			this.indexGrid.on('cellclick',function(grid, cell, columnIndex, record , node , rowIndex , evt){
				var column = grid.getHeaderCt().getHeaderAtIndex(columnIndex).colid;

				if(record.get('primary'))
					return;

				switch(column){

					case 'editcolumn' :
						if(!app.crud.orm.canEdit){
							return;
						}
						this.showEditIndex(record.get('name'));
						break;
					case 'deleteindex':
						if(!app.crud.orm.canDelete){
							return;
						}
						this.deleteIndex(record.get('name'));
						break;
				}

			},this);

			this.dataGrid.on('cellclick',function(grid, cell, columnIndex, record , node , rowIndex , evt){
				var column = grid.getHeaderCt().getHeaderAtIndex(columnIndex).colid;

				if(record.get('system'))
					return;

				switch(column){
					case 'editcolumn' :
						if(!app.crud.orm.canEdit){
							return;
						}
						this.showEditField(record.get('name'));
						break;
					case 'deleteindex':
						if(!app.crud.orm.canDelete){
							return;
						}
						this.deleteField(record.get('name'));
						break;
					case 'link_type':
						if(!app.crud.orm.canEdit){
							return;
						}
						if(record.get('link_type') == 'dictionary'){
							this.fireEvent('showdictionarywin',record.get('object'));
						}
						break;
				}

			},this);
		}

		var me = this;

		this.configForm = Ext.create('Ext.form.Panel',{
			bodyPadding:3,
			frame:false,
			title:appLang.CONFIG,
			bodyCls:'formBody',
			bodyBorder:false,
			border:false,
            autoScroll:true,
			fieldDefaults: {
				labelWidth: 180,
				labelAlign:'right',
				width:400
			},
			items:[{
				xtype:'checkbox',
				name:'rev_control',
				fieldLabel:appLang.VC,
				value:0,
				width:200,
				listeners:{
					render:{fn:this.initTooltip,scope:this}
				}
			},{
				xtype:'fieldcontainer',
                fieldLabel:appLang.HISTORY_LOG,
                combineErrors: true,
                msgTarget : 'side',
                layout: 'hbox',
				items:[
					{
						xtype:'checkbox',
                        width:20,
                        labelWidth:1,
                        hideLabel:true,
						name:'save_history',
						listeners:{
							render:{fn:this.initTooltip,scope:this},
                            change:function(box, value){
                                var relatedField =this.configForm.getForm().findField('log_detalization');
                                if(value){
                                    relatedField.show();
                                }else{
                                    relatedField.hide();
                                }
                            },
                            scope:this
						}
					},{
                        xtype:'combobox',
                        name:'log_detalization',
                        fieldLabel:appLang.HISTORY_LOG_DETALIZATION,
                        displayField:'title',
                        valueField:'id',
                        queryMode:'local',
                        forceSelection:true,
                        labelWidth:70,
                        allowBlank:false,
                        hidden:true,
                        value:'default',
                        width:195,
                        store:Ext.create('Ext.data.Store', {
                            model:'app.comboStringModel',
                            data:[
                                {id:'default' , title:"Default"},
                                {id:'extended' ,title:"Extended"}
                            ]
                        })
                    }
				]
			}, {
				xtype:'hiddenfield',
				name:'parent_object'
			},{
                xtype:'hiddenfield',
                name:'data_object'
            },{
				xtype:'textfield',
				name:'name',
				fieldLabel:appLang.OBJECT_NAME,
				allowBlank:false,
				vtype:'alphanum',
				listeners:{
					render:{fn:this.initTooltip,scope:this}
				}
			},{
				xtype:'textfield',
				name:'title',
				fieldLabel:appLang.TITLE,
				allowBlank:false,
				listeners:{
					render:{fn:this.initTooltip,scope:this}
				}
			},{
				xtype:'textfield',
				name:'table',
				fieldLabel:appLang.TABLE_NAME,
				allowBlank:false,
				vtype:'alphanum',
				listeners:{
					render:{fn:this.initTooltip,scope:this}
				}
			},
				{
					xtype:'combobox',
					name:'engine',
					fieldLabel:appLang.TABLE_ENGINE,
					displayField:'title',
					valueField:'title',
					queryMode:'local',
					forceSelection:true,
					value:'InnoDB',
					store:Ext.create('Ext.data.Store', {
						model:'app.comboStringModel',
						data:[
							{id:'myisam' , title:"MyISAM"},
							{id:'innodb' ,title:"InnoDB"},
							{id:'memory' ,title:"Memory"}
						]
					}),
					allowBlank:false,
					listeners:{
						'change':{
							fn:function(combo, newValue, oldValue){

								if(!Ext.isEmpty(newValue)){
									this.checkFieldsValidation(newValue);
								}

								var keysField = this.configForm.getForm().findField('disable_keys');

								if(newValue == 'InnoDB'){
									keysField.show();
								}else{
									keysField.hide();
								}
							},
							scope:this
						},
						render:{fn:this.initTooltip,scope:this}
					}
				},{
					xtype:'checkbox',
					name:'disable_keys',
					fieldLabel:appLang.DISABLE_KEYS,
					value:0,
					hidden:false,
					listeners:{
						render:{fn:this.initTooltip,scope:this}
					}
				},{
					xtype:'combobox',
					name:'link_title',
					fieldLabel:appLang.MSG_FIELD_USED_AS_TITLE,
					store:this.dataStore,
					displayField:'title',
					valueField:'name',
					hidden:this.objectName?false:true,
					allowBlank:true,
					listeners:{
						render:{fn:this.initTooltip,scope:this}
					}
				},{
					xtype:'displayfield',
					fieldLabel:' ',
					labelSeparator:'',
					value:appLang.ADVANCED_OPTIONS +':'
				},{
					xtype:'combobox',
					name:'connection',
					fieldLabel:appLang.DB_CONNECTION,
					queryMode:'local',
					displayField:'id',
					forceSelection:true,
					allowBlank:false,
					valueField:'id',
					value:'default',
					store:Ext.create('Ext.data.Store',{
						fields:[{name:'id' , type:'string'}],
						autoLoad:true,
						proxy: {
							type: 'ajax',
							url:app.crud.orm.Actions.listConnections,
							reader: {
								type: 'json',
								rootProperty: 'data',
								idProperty: 'id'
							}
						},
						remoteSort:false,
						sorters: [{
							property : 'id',
							direction: 'ASC'
						}]
					}),
					listeners:{
						render:{fn:this.initTooltip,scope:this}
					}
				},{
					xtype:'textfield',
					allowBlank:false,
					vtype:'alpha',
					value:'id',
					fieldLabel:appLang.PRIMARY_KEY,
					name:'primary_key',
					listeners:{
						render:{fn:this.initTooltip,scope:this}
					}
				},{
					xtype:'checkbox',
					name:'readonly',
					hidden:true,
					fieldLabel:appLang.DB_READONLY,
					labelAlign:'right',
					listeners:{
						render:{fn:this.initTooltip,scope:this},
						'change':{
							fn:function(field, newValue, oldValue, options ){
								var form = this.configForm.getForm();
								if(newValue){
									form.findField('locked').hide();
								}else{
									form.findField('locked').show();
								}
							},
							scope:this
						}

					}
				},{
					xtype:'checkbox',
					name:'locked',
					hidden:true,
					fieldLabel:appLang.DB_STRUCTURE_LOCKED,
					listeners:{
						render:{fn:this.initTooltip,scope:this}
					}
				},{
					xtype:'checkbox',
					name:'use_db_prefix',
					fieldLabel:appLang.DB_USE_PREFIX,
					checked:true,
					listeners:{
						render:{fn:this.initTooltip,scope:this}
					}
				},{
                    xtype:'checkbox',
                    name:'distributed',
                    fieldLabel:appLang.DISTRIBUTED,
                    value:0,
                    width:200,
                    hidden:!this.sharding,
					disabled:true,
                    listeners:{
                        render:{fn:this.initTooltip,scope:this},
                        change:function(box, value){
                            var form = me.configForm.getForm();
                            if(value){
                                if(!Ext.isEmpty(this.objectName)){
                                    me.distributedIndexGrid.enable();
                                }
                                form.findField('sharding_type').show();
                                form.findField('sharding_type').enable();
                                form.findField('rev_control').setValue(false);
                                form.findField('rev_control').disable();
                            }else{
                                me.distributedIndexGrid.disable();
                                form.findField('sharding_type').hide();
                                form.findField('sharding_type').disable();
                                form.findField('rev_control').enable();
                            }
                        },
                        scope:this
                    }
                },{
                    xtype:'combobox',
                    name:'sharding_type',
                    fieldLabel:appLang.ORM_SHARDING_TYPE,
                    queryMode:'local',
                    displayField:'title',
                    forceSelection:true,
                    allowBlank:false,
                    valueField:'id',
                    hidden:true,
                    disabled:true,
                    value:'global_id',
                    listeners:{
                        render:{fn:this.initTooltip,scope:this},
                        change:function(box, value){
                            var form = this.configForm.getForm();
                            var field = form.findField('sharding_key');
                            if(value == 'sharding_key' || value == 'sharding_key_no_index' || value == 'virtual_bucket'){
                                field.show();
                                field.enable();
                            }else{
                                field.hide();
                                field.reset();
                                field.disable();
                            }
                        },
                        scope:this
                    },
                    store:Ext.create('Ext.data.Store',{
                        model:'app.comboStringModel',
                        autoLoad:true,
                        proxy: {
                            type: 'ajax',
                            url: app.crud.orm.Actions.listShardingTypes,
                            reader: {
                                type: 'json',
                                rootProperty: 'data',
                                idProperty: 'id'
                            }
                        },
                        remoteSort:false,
                        sorters: [{
                            property : 'title',
                            direction: 'ASC'
                        }]
                    }),
                },{
                    xtype:'combobox',
                    name:'sharding_key',
                    fieldLabel:appLang.ORM_SHARDING_KEY,
                    queryMode:'remote',
                    displayField:'title',
                    forceSelection:true,
                    allowBlank:false,
                    valueField:'id',
                    hidden:true,
                    disabled:true,
                    value:'global_id',
                    listeners:{
                        render:{fn:this.initTooltip,scope:this}
                    },
                    store:Ext.create('Ext.data.Store',{
                        model:'app.comboStringModel',
                        autoLoad:true,
                        proxy: {
                            type: 'ajax',
                            extraParams:{
                               object: Ext.isEmpty(this.objectName)?'':this.objectName
                            },
                            url: app.crud.orm.Actions.listShardingFields,
                            reader: {
                                type: 'json',
                                rootProperty: 'data',
                                idProperty: 'id'
                            }
                        },
                        remoteSort:false,
                        sorters: [{
                            property : 'title',
                            direction: 'ASC'
                        }]
                    }),
                }
			]
		});

		if(!Ext.isEmpty(app.crud.orm.objectFields)){
			this.configForm.add(app.crud.orm.objectFields);
		}

		this.configForm.add({
            xtype:'fieldcontainer',
            fieldLabel:' ',
            labelSeparator:'',
            items:mainConfButtons
        });

		this.tabPanel = Ext.create('Ext.tab.Panel',{
			deferredRender:true,
			items:[this.configForm]
		});

		/*
		 * Add Fields tab if object exists
		 * and load fields store
		 */
		if(this.objectName){
			this.tabPanel.add(this.dataGrid);
			this.tabPanel.setActiveTab(1);
			this.tabPanel.add(this.indexGrid);

			this.dataStore.load();
			this.indexStore.load();

            if(this.sharding) {
                this.tabPanel.add(this.distributedIndexGrid);
                this.distributedIndexStore.load();
            }
		}

		this.items = [this.tabPanel];

		this.callParent(arguments);

		if(this.sharding){
            this.distributedIndexGrid.disable();
        }

		var form = this.configForm.getForm();
		form.findField('primary_key').setReadOnly(true);
		form.findField('primary_key').setFieldStyle({color:"#808080"});


		if(this.isSystem || this.isExternal)
		{
			form.findField('name').setReadOnly(true);
			form.findField('name').setFieldStyle({color:"#808080"});
		}

		if(this.objectName){
			var handle = this;
			handle.configForm.getForm().findField('readonly').show();
			handle.configForm.getForm().findField('locked').show();
            if(handle.sharding){
                handle.configForm.getForm().findField('distributed').enable();
            }
			this.on('show' , function(){
				var params = Ext.apply({object:this.objectName});
				this.configForm.getForm().load({
					url:app.crud.orm.Actions.loadObjCfg,
					params:params,
					waitMsg:appLang.LOADING,
					success: function(form, action)
					{
						if(!action.result.success)
						{
							if(action.result.errors !== false)
							{
								var msg = '';
								if(!Ext.isEmpty(action.result.errors.indexes)){
									msg += appLang.UNAVAILABLE_INDEXES + ' ' + action.result.errors.indexes.join();
								}
								msg += ' ';
								if(!Ext.isEmpty(action.result.errors.fields)){
									msg += appLang.UNAVAILABLE_FIELDS + ' ' + action.result.errors.fields.join();
								}
								Ext.Msg.alert(appLang.MESSAGE, msg);
							}else{
								Ext.Msg.alert(appLang.MESSAGE, action.result.msg);
							}
						}


					},
					failure: app.formFailure
				});
			},this);
		}
	},
	initTooltip:function(field){
		var name = field.name;
		var qTipName = 'qtip_object_' + name;

		if(Ext.isEmpty(ormTooltips[qTipName]))
			return;

		Ext.create('Ext.tip.ToolTip', {
			target:field.getEl(),
			html:ormTooltips[qTipName]
		});
	},
	checkFieldsValidation:function(storageEngine){
		var msg = '';
		var xFields = [];
		var xIndexes = [];
		switch(storageEngine.toLowerCase()){
			case 'innodb':
				this.indexStore.each(function(rec){
					if (rec.get('fulltext')){
						xIndexes.push(rec.get('name'));
					}
				},this);
				break;

			case 'memory':
				this.dataStore.each(function(rec){
					var type = rec.get('type');
					if (Ext.Array.indexOf(app.crud.orm.textTypes , type)!=-1||
						Ext.Array.indexOf(app.crud.orm.blobTypes , type)!=-1)
					{
						xFields.push(rec.get('name'));
					}
				},this);

				this.indexStore.each(function(rec){
					if (rec.get('fulltext')){
						xIndexes.push(rec.get('name'));
					}
				},this);
				break;

			default:   	    return;
				break;
		}

		if(xIndexes.length){
			msg += appLang.UNAVAILABLE_INDEXES + ' ('+oldValue+'): ' + xIndexes.join(', ');
		}
		msg += '<br>';
		if(xFields.length){
			msg += appLang.UNAVAILABLE_FIELDS + ' ('+oldValue+'): ' + xFields.join(', ');
		}
		if(msg.length > 4){
			combo.setValue(oldValue);
			Ext.Msg.alert(appLang.MESSAGE, msg);
		}
	},
	saveMainCfg: function()
	{
		var handle = this;

		if(!this.configForm.getForm().isValid()){
			return;
		}

		var isNew = false;
		if(Ext.isEmpty(handle.objectName)){
			isNew = true;
		}

		this.configForm.getForm().submit({
			clientValidation: true,
			waitMsg: appLang.SAVING,
			method:'post',
			url:app.crud.orm.Actions.saveObjCfg,
			params:{record_id:this.objectName,'rev_control':this.configForm.getForm().findField('rev_control').getValue()},
			success: function(form, action){
				/*
				 * Define objectName property , load fields store, add Fields tab
				 * if new object has been created
				 */
				handle.objectName = handle.configForm.getForm().findField('name').getValue();

				handle.dataStore.proxy.setExtraParam('object' , handle.objectName);
				handle.indexStore.proxy.setExtraParam('object' , handle.objectName);

				var configForm = handle.configForm.getForm();
				if(isNew)
				{
					handle.tabPanel.add(handle.dataGrid);
					handle.tabPanel.add(handle.indexGrid);
                    configForm.findField('readonly').show();
                    configForm.findField('locked').show();
                    handle.distributedIndexGrid.disable();
                    handle.distributedIndexStore.proxy.setExtraParam('object' , handle.objectName);
                    handle.tabPanel.add(handle.distributedIndexGrid);
                    handle.distributedIndexGrid.disable();

				}
                configForm.findField('link_title').show();
                configForm.findField('sharding_key').getStore().proxy.extraParams['object'] = handle.objectName;
                if(handle.sharding){
                    configForm.findField('distributed').enable();
                }

				handle.dataStore.load();
				handle.indexStore.load();
				if(configForm.findField('distributed').getValue()){
                    handle.distributedIndexStore.load();
                }
				handle.setTitle(appLang.EDIT_OBJECT + ' &laquo;' + handle.objectName + '&raquo; ');

				handle.fireEvent('dataSaved');
			},
			failure:function(form, action){
				if(action.result &&  action.result.errors !== false){
					var msg = '';

					if(!Ext.isEmpty(action.result.msg)){
						msg = action.result.msg + ' ';
					}

					if(!Ext.isEmpty(action.result.errors.indexes)){
						msg += appLang.UNAVAILABLE_INDEXES + ': ' + action.result.errors.indexes.join(', ');
					}
					msg += '<br>';
					if(!Ext.isEmpty(action.result.errors.fields)){
						msg += appLang.UNAVAILABLE_FIELDS + ': ' + action.result.errors.fields.join(', ');
					}
					Ext.Msg.alert(appLang.MESSAGE, msg);
				}else{
					Ext.Msg.alert(appLang.MESSAGE, action.result.msg);
				}
			}
		});
	},
	/**
	 * Send delete index request
	 * @param string name
	 */
	deleteIndex:function(name){
		var handle = this;
		Ext.Msg.confirm(appLang.CONFIRM, appLang.MSG_CONFIRM_DELETE +' "'+name+'"?', function(btn){
			if(btn != 'yes'){
				return;
			}
			Ext.Ajax.request({
				url: app.crud.orm.Actions.deleteIndex,
				method: 'post',
				params:{
					'object':this.objectName,
					'name':name
				},
				success: function(response, request) {
					response =  Ext.JSON.decode(response.responseText);
					if(response.success){
						handle.indexStore.load();
						handle.fireEvent('indexRemoved');
					}else{
						Ext.Msg.alert(appLang.MESSAGE, response.msg);
					}
				},
				failure:app.formFailure
			});
		},this);
	},
    /**
     * Delete distributed index
     * @param name
     */
    deleteDistributedIndex:function (name) {
        var handle = this;
        Ext.Msg.confirm(appLang.CONFIRM, appLang.MSG_CONFIRM_DELETE +' "'+name+'"?', function(btn){
            if(btn != 'yes'){
                return;
            }
            Ext.Ajax.request({
                url: app.crud.orm.Actions.deleteDistIndex,
                method: 'post',
                params:{
                    'object':this.objectName,
                    'name':name
                },
                success: function(response, request) {
                    response =  Ext.JSON.decode(response.responseText);
                    if(response.success){
                        handle.distributedIndexStore.load();
                        handle.fireEvent('distributedIndexRemoved');
                    }else{
                        Ext.Msg.alert(appLang.MESSAGE, response.msg);
                    }
                },
                failure:app.formFailure
            });
        },this);
    },
	/**
	 * Send delete field request
	 * @param string name
	 */
	deleteField:function(name){
		var handle = this;
		Ext.Msg.confirm(appLang.CONFIRM, appLang.MSG_CONFIRM_DELETE +' "'+name+'"?', function(btn){
			if(btn != 'yes'){
				return;
			}
			Ext.Ajax.request({
				url: app.crud.orm.Actions.deleteField,
				method: 'post',
				params:{
					'object':this.objectName,
					'name':name
				},
				success: function(response, request) {
					response =  Ext.JSON.decode(response.responseText);
					if(response.success){
						handle.dataStore.load();
						handle.indexStore.load();
						handle.fireEvent('fieldRemoved');
					}else{
						Ext.Msg.alert(appLang.MESSAGE , response.msg);
					}
				},
				failure:app.formFailure
			});
		},this);
	},
	showEditField:function(id){

		var win = Ext.create('app.crud.orm.FieldWindow',{
			objectName:this.objectName,
			fieldName:id,
			title:appLang.EDIT_FIELD,
			objectList:this.objectList(),
			dictionaryUrl:app.crud.orm.Actions.dictionary
		});

		win.setTableEngine(this.configForm.getForm().findField('engine').getValue());

		win.on('dataSaved',function(){
			this.dataStore.load();
			this.indexStore.load();
			this.fireEvent('dataSaved');
		},this);
		win.show();
	},
	showEditIndex:function(index){

		var cols = [];

		this.dataStore.each(function(rec){
			if(rec.get('link_type')!=='multi'){
                cols.push(rec.get('name'));
            }
		},this);

		var win = Ext.create('app.crud.orm.IndexWindow',{
			objectName:this.objectName,
			indexName:index,
			columnsList:cols,
			title:appLang.EDIT_INDEX
		});

		win.setTableEngine(this.configForm.getForm().findField('engine').getValue());

		win.on('dataSaved',function(){
			this.dataStore.load();
			this.indexStore.load();
			this.fireEvent('dataSaved');
		},this);

		win.show();
	},
	encryptData:function(objectName){
		var me = this;
		var objectTitle = this.configForm.getForm().findField('title').getValue();
		Ext.Msg.confirm(appLang.CONFIRM, appLang.MSG_CONFIRM_ENCRYPT +' "' + objectTitle + '" ?', function(btn){
			if(btn != 'yes'){
				return;
			}

			Ext.Ajax.request({
				url: app.crud.orm.Actions.encryptData,
				method: 'post',
				scope:this,
				timeout:1000*60*60*24,
				params:{
					'object':objectName
				},
				success: function(response, request) {
					response =  Ext.JSON.decode(response.responseText);
					if(!response.success){
						Ext.Msg.alert(appLang.MESSAGE , response.msg);
					}
				}
			});

			var win = Ext.create('app.crud.orm.taskStatusWindow',{
				title: '"'+objectTitle+ '" '+appLang.ENCRYPT_DATA,
				controllerUrl:app.crud.orm.Actions.encTaskStat,
				extraParams:{
					'object':objectName,
					'type':'encrypt'
				}
			});

			win.on('failure' , function(msg){
				Ext.Msg.alert(appLang.MESSAGE , msg);
				win.close();
			} , me);

			win.on('finished' , function(){
				this.dataGrid.getStore().load();
			}, me);

			win.show();

		},this);
	},
	decryptData:function(objectName){
		var me = this;
		var objectTitle = this.configForm.getForm().findField('title').getValue();
		Ext.Msg.confirm(appLang.CONFIRM, appLang.MSG_CONFIRM_DECRYPT +' "'+objectTitle+'" ?', function(btn){
			if(btn != 'yes'){
				return;
			}

			Ext.Ajax.request({
				url: app.crud.orm.Actions.decryptData,
				method: 'post',
				scope:this,
				timeout:1000*60*60*24,
				params:{
					'object':this.objectName
				},
				success: function(response, request) {
					response =  Ext.JSON.decode(response.responseText);
					if(!response.success){
						Ext.Msg.alert(appLang.MESSAGE , response.msg);
					}
				}
			});

			var win = Ext.create('app.crud.orm.taskStatusWindow',{
				title: '"'+objectTitle+ '" '+appLang.DECRYPT_DATA,
				controllerUrl:app.crud.orm.Actions.encTaskStat,
				extraParams:{
					'object':this.objectName,
					'type':'decrypt'
				}
			});

			win.on('failure' , function(msg){
				Ext.Msg.alert(appLang.MESSAGE , msg);
				win.close();
			} , me);

			win.on('finished' , function(){
				this.dataGrid.getStore().load();
			}, me);

			win.show();
		},this);
	},
    addDistributedIndex:function(value){
        var me = this;
        Ext.Ajax.request({
            url: app.crud.orm.Actions.addDistributedIndex,
            method: 'post',
            params:{
                'object':this.objectName,
                'field':value
            },
            success: function(response, request) {
                response =  Ext.JSON.decode(response.responseText);
                if(response.success){
                    me.distributedIndexStore.load();
                    me.fireEvent('distributedIndexAdded');
                }else{
                    Ext.Msg.alert(appLang.MESSAGE, response.msg);
                }
            },
            failure:app.formFailure
        });
    }
});