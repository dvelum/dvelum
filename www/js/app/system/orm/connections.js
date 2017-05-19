Ext.ns('app.orm.connections');
//root:
/**
 *
 * @event dataSaved
 *
 */
Ext.define('app.orm.connections.Window',{
	extend:'Ext.Window',

	dataGrid:null,
	dataStore:null,

	controllerUrl:'',
	dbConfigs:null,
	modal:true,

	constructor: function(config) {
		config = Ext.apply({
			title: appLang.DB_CONNECTIONS,
			width: 800,
			height:300,
			layout:'fit',
			closeAction: 'destroy',
			maximizable:true
		}, config || {});
		this.callParent(arguments);
	},

	initComponent:function(){
		var me = this;
		var defaultConnectionId = this.dbConfigs[0]['id'];

		this.dataStore = Ext.create('Ext.data.Store', {
			fields: [
				{name:'id' , type:'string'},
				{name:'system', type:'boolean'},
				{name:'devType', type:'integer'},
				{name:'username' , type:'string'},
				{name:'dbname' , type:'string'},
				{name:'host', type:'string'},
				{name:'adapter', type:'string'},
                {name:'isolation', type:'string'}
			],
			proxy: {
				type: 'ajax',
				url:this.controllerUrl +  'list',
				extraParams:{
					devType:defaultConnectionId
				},
				reader: {
					type: 'json',
					rootProperty: 'data',
					idProperty: 'id'
				},
				simpleSortMode: true
			},
			remoteSort: false,
			autoLoad: true,
			sorters: [{
				property : 'id',
				direction: 'DESC'
			}]
		});

		this.filter = Ext.create('Ext.form.field.ComboBox',{
			typeAhead: true,
			triggerAction: 'all',
			selectOnTab: true,
			labelWidth:80,
			forceSelection:true,
			queryMode:'local',
			displayField:'title',
			valueField:'id',
			value:defaultConnectionId,
			store:Ext.create('Ext.data.Store',{
				fields: [
					{name:'id' ,  type:'integer'},
					{name:'title' ,  type:'string'}
				],
				data:this.dbConfigs
			}),
			listeners:{
				change:{
					fn:function(){
						this.dataStore.proxy.setExtraParam('devType' , this.filter.getValue());
						this.dataStore.load();
					},
					scope:this
				}
			}
		});


		this.dataGrid = Ext.create('Ext.grid.Panel',{
			store: this.dataStore,
			frame: false,
			loadMask:true,
			columnLines: true,
			scrollable:true,
			forceFit:true,
			viewConfig:{
                stripeRows:false,
				enableTextSelection: true
			},
			tbar:[
				{
					text:appLang.ADD_ITEM,
					handler:function(){
						this.showEditWindow(false , this.filter.getValue());
					},
					scope:this
				},'-',
				appLang.TYPE,
				this.filter

			],
			columns: [
				{
					xtype:'actioncolumn',
                    width:30,
                    align:'center',
                    dataIndex:'id',
					items:[
						{
							iconCls:'editIcon',
							scope:this,
							handler:function(grid, rowIndex, colIndex){
								var rec = this.dataStore.getAt(rowIndex);
								this.showEditWindow(rec.get('id') , rec.get('devType'));
							}
						}
					]
				},{
					text:appLang.NAME,
					dataIndex:'id'
				},{
					sortable: true,
					text:appLang.DB_HOST,
					dataIndex:'host',
					align:'center'
				},{
					sortable: true,
					text:appLang.DB_NAME,
					dataIndex: 'dbname',
					align:'center'
				},{
					sortable: true,
					text:appLang.USER,
					dataIndex: 'username',
					align:'center'
				},{
					sortable: true,
					text:appLang.DB_ADAPTER,
					dataIndex: 'adapter',
					align:'center'
				},{
                    sortable: true,
                    text:appLang.TRANSACTION_ISOLATION_LEVEL,
                    dataIndex: 'isolation',
                    align:'center'
                },{
					width:30,
					align:'center',
					dataIndex:'system',
					colid:'deleteindex',
					renderer:function(value, metaData, record, rowIndex, colIndex, store){
						if(value) {
							return '<img src="'+app.wwwRoot+'i/system/locked.png" title="'+appLang.SYSTEM_PROTECTED_FIELD+'">';
						}else{
							return '<img src="'+app.wwwRoot+'i/system/delete.png" title="'+appLang.DELETE_ITEM+'" style="cursor:pointer;">';
						}
					}
				}

			],
			listeners : {
				'itemdblclick':{
					fn:function(view , record , number , event , options){

						this.showEditWindow(record.get('id') , record.get('devType'));
					},
					scope:this
				},
				'cellclick':{
					fn:function(grid, cell, columnIndex, record , node , rowIndex , evt){
						var column = grid.getHeaderCt().getHeaderAtIndex(columnIndex).colid;

						if(record.get('primary'))
							return;

						switch(column){
							case 'deleteindex':
								if(record.get('system')){
									return;
								}
								this.removeItem(record);
								break;
						}

					},
					scope:this
				}
			}
		});
		this.items = [this.dataGrid];
		this.callParent();
		this.on('show',function(){app.checkSize(this);});
	},
	/**
	 * Remove database connection
	 * @param grid
	 * @param rowIndex
	 * @param colIndex
	 */
	removeItem:function(record){
		Ext.Ajax.request({
			url:this.controllerUrl +  'remove',
			method: 'post',
			scope:this,
			params:{'id':record.get('id')},
			success: function(response, request) {
				response =  Ext.JSON.decode(response.responseText);
				if(response.success){
					this.dataStore.remove(record);
				} else {
					Ext.Msg.alert(appLang.MESSAGE , response.msg);
				}
			},
			failure:function() {
				Ext.Msg.alert(appLang.MESSAGE , appLang.MSG_LOST_CONNECTION);
			}
		});
	},
	/**
	 * Show connection editor window
	 * @param record
	 */
	showEditWindow:function(id , devtype){
		var win = Ext.create('app.orm.connections.EditWindow',{
			recordId:id,
			devType:devtype,
			controllerUrl:this.controllerUrl
		});

		win.on('dataSaved',function(){
			this.dataStore.load();
		},this);

		win.show();
	}
});

Ext.define('app.orm.connections.EditWindow',{
	extend:'Ext.Window',

	dataForm:null,
	controllerUrl:'',
	devType:false,
	recordId:false,

	constructor: function(config) {
		config = Ext.apply({
			title: appLang.DB_CONNECTIONS,
			width: 400,
			height:450,
			layout:'fit',
			modal:true,
			closeAction: 'destroy',
			maximizable:false
		}, config || {});
		this.callParent(arguments);
	},
	initComponent:function(){
		this.dataForm = Ext.create('Ext.form.Panel',{
			bodyCls:'formBody',
			bodyPadding:10,
			fieldDefaults:{
				anchor:'100%',
				labelWidth:160
			},
			items:[

				{
					xtype:'textfield',
					fieldLabel:appLang.NAME,
					allowBlank:false,
					vtype:'alphanum',
					value:'',
					name:'id'
				},{
					xtype:'textfield',
					fieldLabel:appLang.DB_HOST,
					allowBlank:false,
					name:'host'
				},{
					xtype:'textfield',
					fieldLabel:appLang.DB_PORT,
					allowBlank:true,
					name:'port'
				},{
					xtype:'textfield',
					fieldLabel:appLang.DB_NAME,
					allowBlank:false,
					name:'dbname'
				},{
					xtype:'textfield',
					fieldLabel:appLang.DB_CHARSET,
					allowBlank:false,
					value:'UTF8',
					name:'charset'
				},{
					xtype:'textfield',
					fieldLabel:appLang.DB_PREFIX,
					name:'prefix'
				},
				Ext.create('Ext.form.field.ComboBox',{
					xtype:'combo',
					name:'adapter',
					fieldLabel:appLang.DB_ADAPTER,
					queryMode:'local',
					allowBlank:false,
					forceSelection:true,
					displayField:'title',
					valueField:'title',
					value:'Mysqli',
					store:Ext.create('Ext.data.Store',{
						fields:[{name:'title' , type:'string'}],
						data:[{title:'Mysqli'}],
						sorters: [{
							property : 'title',
							direction: 'ASC'
						}]
					})//adapterNamespace
				}),{
					xtype:'textfield',
					fieldLabel:appLang.USER,
					allowBlank:false,
					name:'username'
				},{
					name: 'setpass',
					value: 1,
					readOnly:true,
					fieldLabel:appLang.CHANGE_PASSWORD,
					submitValue:true,
					checked:true,
					xtype:'checkbox',
					listeners: {
						change : {
							fn:this.denyBlankPassword,
							scope:this,
							buffer:350
						}
					}
				},{
					fieldLabel:appLang.PASSWORD,
					inputType:"password",
					name:"password",
					xtype:"textfield",
					enableKeyEvents:true,
					allowBlank:false
				},{
					fieldLabel:appLang.PASSWORD_CONFIRM,
					inputType:"password",
					name:"pass2",
					submitValue:false,
					xtype:"textfield",
					enableKeyEvents:true,
					vtype: 'password',
					initialPassField: 'password',
					allowBlank:false
				},
                Ext.create('Ext.form.field.ComboBox',{
                    xtype:'combo',
                    name:'transactionIsolationLevel',
                    fieldLabel:appLang.TRANSACTION_ISOLATION_LEVEL,
                    queryMode:'local',
                    allowBlank:false,
                    forceSelection:true,
                    displayField:'title',
                    valueField:'title',
                    value:'default',
                    store:Ext.create('Ext.data.Store',{
                        fields:[{name:'title' , type:'string'}],
                        data:[
                            {title:'default'},
                            {title:'READ UNCOMMITTED'},
                            {title:'READ COMMITTED'},
                            {title:'REPEATABLE READ'},
                            {title:'SERIALIZABLE'}
                        ],
                        sorters: [{
                            property : 'title',
                            direction: 'ASC'
                        }]
                    })//adapterNamespace
                }),
			]
		});

		this.buttons = [
			{
				text:appLang.TEST,
				scope:this,
				handler:this.testAction
			},{
				text:appLang.SAVE,
				scope:this,
				handler:this.saveAction
			},{
				text:appLang.CANCEL,
				scope:this,
				handler:this.close
			}
		];

		this.items = [this.dataForm];

		this.callParent(arguments);

		if(this.recordId){
			this.dataForm.load({
				url:this.controllerUrl +  'load',
				params:{id:this.recordId,devType:this.devType},
				scope:this,
				success: function(form, action) {
					if(action.result.success){
						form.findField('setpass').enable();
						form.findField('setpass').setValue(false);
						form.findField('setpass').setReadOnly(false);
					} else{
						Ext.Msg.alert(appLang.MESSAGE, action.result.msg);
					}
				},
				failure: app.formFailure
			});
		}
	},
	/**
	 * Permit or rapretit be empty password field
	 * @param {Ext.form.field} field
	 * @param bool bool
	 */
	denyBlankPassword:function(field, bool){
		var handle = this.dataForm.getForm();

		if(!bool){
			handle.findField('password').disable();
			handle.findField('pass2').disable();
		} else {
			handle.findField('password').enable();
			handle.findField('pass2').enable();
		}

		handle.findField('password').allowBlank = !bool;
		handle.findField('pass2').allowBlank = !bool;
	},
	/**
	 * Save db Connection config
	 */
	saveAction:function(){
		this.dataForm.getForm().submit({
			clientValidation: true,
			waitMsg: appLang.SAVING,
			method:'post',
			scope:this,
			url:this.controllerUrl +  'save',
			params:{oldid:this.recordId, devType:this.devType},
			success: function(form, action) {
				if(!action.result.success){
					Ext.Msg.alert(appLang.MESSAGE, action.result.msg);
				} else{
					this.fireEvent('dataSaved');
					this.close();
				}
			},
			failure: app.formFailure
		});
	},
	/**
	 * Test database connection
	 */
	testAction:function(){
		this.dataForm.getForm().submit({
			clientValidation: true,
			waitMsg: appLang.CHECKING,
			method:'post',
			scope:this,
			url:this.controllerUrl +  'test',
			params:{devType:this.devType},
			success: function(form, action) {
				if(action.result.success){
					Ext.Msg.alert(appLang.MESSAGE, appLang.SUCCESS);
				} else{
					Ext.Msg.alert(appLang.MESSAGE, action.result.msg);
				}
			},
			failure: app.formFailure
		});
	}
});