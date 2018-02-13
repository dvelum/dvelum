Ext.define('designer.grid.column.Model',{
	extend:'Ext.data.Model',
	fields: [
		{name:'id' ,  type:'string'},
		{name:'text' , type:'string'},
		{name:'dataIndex',type:'string'},
		{name:'type',type:'string'},
		{name:'editor',type:'string'},
        {name:'filter',type:'string'},
		{name:'order',type:'integer'}
	],
	idProperty:'id'
});

Ext.define('designer.grid.column.Window',{
	extend:'Ext.Window',
	layout:'border',
	dataStore:null,
	width:app.checkWidth(900),
	height:app.checkHeight(650),
	propertiesPanel:null,
	objectName:null,
	controllerUrl:null,
	cellEditing:null,
	dataTree:null,
	dataGrid :null,
	modal:true,
	autoLoadData:true,
	maximizable :true,

	initComponent:function()
	{
		this.controllerUrl = app.createUrl([designer.controllerUrl ,'grid','']);
		this.cellEditing = Ext.create('Ext.grid.plugin.CellEditing', {clicksToEdit:1});
		this.initColumnsGrid();
		this.initColumnsTree();


		this.dataGrid.getSelectionModel().on('selectionchange',function(sm , data , opts){
			//this.cellEditing.completeEdit();
			if(!sm.hasSelection()){
				this.propertiesPanel.resetProperties();
				this.propertiesPanel.refreshEvents();
				return;
			}
            var colId =  sm.getSelection()[0].get('id');
            this.propertiesPanel.setExtraParams({'id':colId,'columnId':colId});
			this.propertiesPanel.loadProperties();
            this.propertiesPanel.refreshEvents();
		},this);

		this.dataTree.getSelectionModel().on('selectionchange',function(sm){
			this.cellEditing.completeEdit();
			if(!sm.hasSelection()){
				return;
			}

			var recId = sm.getSelection()[0].get('id');

			var index = this.dataGrid.getStore().findExact('id',recId);
			if(index!=-1){
				this.dataGrid.getSelectionModel().select(index);
			}
		},this);

		this.propertiesPanel =  Ext.create('designer.properties.GridColumn',{
			controllerUrl: app.createUrl([designer.controllerUrl ,'gridcolumn','']),
            eventsControllerUrl:app.createUrl([designer.controllerUrl ,'gridcolumnevents','']),
			objectName:this.objectName,
			autoLoad:false,
			listeners:{
				dataSaved:{
					fn:function(){},
					scope:this
				}
			},
			title:desLang.properties,
			layout:'fit',
			region:'east',
			showEvents:true,
			split:true,
			width:250
		});

		this.propertiesPanel.dataGrid.on('propertychange',function(source, recordId, value){
			if(recordId == 'text'){
				this.treeStore.load();
				var gridStore = this.dataGrid.getStore();
				var index = gridStore.findExact('id' , recordId);
				if(index!==-1){
					var record = gridStore.getAt(index);
					if(record.get('text')!==value){
						record.set('text', value);
						record.commit();
					}
				}
			}
		},this);

		this.items = [this.dataTree , this.dataGrid, this.propertiesPanel];

		this.callParent(arguments);

		this.reload();
		this.on('show',function(){app.checkSize(this);});
	},
	/**
	 * Create Columns grid
	 */
	initColumnsGrid:function(){

		this.dataStore = Ext.create('Ext.data.Store',{
			proxy: {
				type: 'ajax',
				url:this.controllerUrl+'columnlist',
				reader: {
					type: 'json',
					idProperty: 'id',
					rootProperty:'data'
				},
				extraParams:{
					object:this.objectName
				}
			},
			model:'designer.grid.column.Model',
			autoLoad:true,
			sorters: [{
				property : 'order',
				direction: 'ASC'
			}]
		});

		this.dataGrid = Ext.create('Ext.grid.Panel',{
			tbar:[{
				tooltip:desLang.addColumn,
				iconCls:'plusIcon',
				scope:this,
				handler:this.addColumn
			},{
				tooltip:desLang.importStore,
				iconCls:'importDbIcon',
				scope:this,
				handler:this.importFieldsFromStore
			}],
			store:this.dataStore,
			region:'center',
			split:true,
			title:desLang.columns,
			columnLines:true,
			columns:[
				{
					xtype:'actioncolumn',
					width:30,
                    align:'center',
					items:[
						{
							iconCls:'filterIcon',
							tooltip:desLang.configureFilter,
							scope:this,
							handler:function(grid, rowIndex, colIndex){
                                var rec = grid.getStore().getAt(rowIndex);
                                this.showFilterWindow(rec.get('id'));
                            }
						}
					]
				},
				{
					xtype:'actioncolumn',
					width:30,
                    align:'center',
					items:[
						{
							iconCls:'fieldIcon',
							tooltip:desLang.configureEditor,
							scope:this,
							handler:this.showEditorConfig
						}
					]
				},{
					text:desLang.editor,
					dataIndex:'editor',
					width:100
				},{
                    align:'center',
                    text:desLang.filter,
                    dataIndex:'filter',
                    width:60
                },
				{
					dataIndex:'text' ,
					text:desLang.header,
					flex:1,
					editor:{
						xtype:'textfield',
						flex:1,
						width:200,
						editable:true
					}
				},
				{
					dataIndex:'dataIndex' , text:desLang.dataIndex,
					editable:true,
					editor:{
						xtype: 'combobox',
						typeAhead: true,
						triggerAction: 'all',
						selectOnTab: true,
						forceSelection:true,
						valueField:'name',
						displayField:'name',
						queryMode:'local',
						multiSelect:false,
						store:Ext.create('Ext.data.Store', {
							model:'designer.model.fieldsModel',
							proxy: {
								type: 'ajax',
								url:app.createUrl([designer.controllerUrl ,'store','']) +  'allStoreFields',
								reader: {
									type: 'json',
									rootProperty: 'data'
								},
								extraParams:{
									object:this.objectName
								},
								simpleSortMode: true
							},
							remoteSort: true,
							autoLoad: true,
							sorters: [{
								property : 'title',
								direction: 'DESC'
							}]
						}),
						listeners:{
							select:{
								fn:function(combo, record){
									this.propertiesPanel.dataGrid.setProperty('dataIndex',record.get('name'));
								},
								scope:this
							}
						}
					}
				},
				{dataIndex:'type', text:desLang.type ,
					editable:true,
					editor:{
						xtype: 'combobox',
						typeAhead: true,
						triggerAction: 'all',
						selectOnTab: true,
						forceSelection:true,
						store: [
							['' , 'Simple'],
							['action', 'Action'],
							['date','Date'],
							['boolean','Boolean'],
							['number','Number'],
							['template','Template'],
							['check','Check']
						],
						listeners:{
							scope:this,
							select:function(combo, records, eOpts){
								var columnId = this.dataGrid.getSelectionModel().getLastSelected().get('id');
								Ext.Ajax.request({
									url:this.controllerUrl + 'changecoltype/',
									method: 'post',
									params:{
										'object':this.objectName,
										'type':records.get('field1'),
										'columnId':columnId
									},
									scope:this,
									success: function(response, request) {
										response =  Ext.JSON.decode(response.responseText);
										if(response.success){
											this.propertiesPanel.setExtraParams({'id':columnId,'columnId':columnId});
											this.propertiesPanel.loadProperties();
                                            this.propertiesPanel.refreshEvents();
											this.propertiesPanel.resetSerchField();

										}else{
											Ext.Msg.alert(appLang.MESSAGE, response.msg);
											return false;
										}
									},
									failure: app.formFailure
								});
							}
						}
					}
				},
				{
					xtype:'actioncolumn',
					width:30,
					align:'center',
					items:[
						{
							iconCls:'deleteIcon',
							scope:this,
							tooltip:desLang.remove,
							handler:this.removeColumn
						}
					]
				}
			],
			plugins:[this.cellEditing],
			listeners:{
				scope:this,
				edit:function(editor , o){
					if(o.field === 'text'){
						this.propertiesPanel.dataGrid.setProperty('text',o.value);
					}
					this.dataStore.commitChanges();
				}
			}
		});
	},
	/**
	 * Create columns Tree
	 */
	initColumnsTree:function()
	{
		this.treeStore = Ext.create('Ext.data.TreeStore',{
			proxy: {
				type: 'ajax',
				url:this.controllerUrl + 'columnlisttree',
				reader: {
					type: 'json',
					idProperty: 'id'
				},
				extraParams:{
					object:this.objectName
				},
				autoLoad:true
			},
			root: {
				text: '/',
				expanded: true,
				//id:0,
				leaf:false,
				children:[]
			},
			defaultRootId:0,
			clearOnLoad:true,
			autoLoad:true
		});

		this.dataTree = Ext.create('Ext.tree.Panel',{
			region:'west',
			width:200,
			collapsible:true,
			collapseMode:'header',
			collapseFirst:true,
			clearOnLoad:true,
			store:this.treeStore,
			split:true,
			rootVisible:false,
			useArrows: false,
			collapsed:true,
			title:desLang.columnsTree,
			viewConfig:{
				plugins: {
					ptype: 'treeviewdragdrop'
				},
				listeners:{
					drop:{
						fn:this.sortChanged,
						scope:this
					}
				}
			}
		});
	},
	sortChanged:function( node, data, overModel,  dropPosition, options)
	{
		var parentId = 0;
		var parentNode = null;
		if(dropPosition == 'append'){
			parentId = overModel.get('id');
			parentNode = overModel;
		}else{
			parentId = overModel.parentNode.get('id');
			parentNode = overModel.parentNode;
		}
		var childsOrder = [];
		parentNode.eachChild(function(node){
			childsOrder.push(node.getId());
		},this);

		Ext.Ajax.request({
			url:this.controllerUrl + 'columnsort',
			method: 'post',
			params:{
				'id':data.records[0].get('id'),
				'newparent':parentId,
				'order[]' : childsOrder,
				'object':this.objectName
			},
			scope:this,
			success: function(response, request) {
				response =  Ext.JSON.decode(response.responseText);
				if(response.success){
					this.dataStore.load();
					this.fireEvent('dataChanged');
				}else{
					Ext.Msg.alert(appLang.MESSAGE, response.msg);
				}
			},
			failure: app.formFailure
		});
	},
	/**
	 * Remove column action
	 * @param {Ext.grid.Panel }grid
	 * @param integer row
	 * @param integer col
	 */
	removeColumn:function(grid , row , col)
	{
		var record = grid.getStore().getAt(row);
		Ext.MessageBox.confirm(appLang.MESSAGE , desLang.remove + '?',function(btn){
			if(btn !='yes'){
				return;
			}

			Ext.Ajax.request({
				url:this.controllerUrl + 'removecolumn',
				method: 'post',
				params:{
					'id':record.get('id'),
					'object':this.objectName
				},
				scope:this,
				success: function(response, request) {
					response =  Ext.JSON.decode(response.responseText);
					if(response.success){
						this.reload();
					}else{
						Ext.Msg.alert(appLang.MESSAGE, response.msg);
					}
				},
				failure:function() {
					Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
				}
			});
		},this);
	},
	/**
	 * Reload columns tree
	 */
	reload:function(){
		this.treeStore.getRootNode().removeAll();
		this.treeStore.load({});
		this.dataStore.load();
		//this.doLayout();
	},
	/**
	 * Show column editor config window
	 * @param {Ext.grid.Panel }grid
	 * @param integer row
	 * @param integer col
	 */
	showEditorConfig:function(grid , row , col){

		var window = Ext.create('Ext.Window',{
			width:300,
			height:400,
			modal:true,
			title:desLang.configureEditor,
			layout:'fit',
			items:[]
		});

		var colId = grid.getStore().getAt(row).get('id');

		var properties =  Ext.create('designer.properties.GridEditor',{
			controllerUrl:app.createUrl([designer.controllerUrl ,'editor','']),
			eventsControllerUrl:app.createUrl([designer.controllerUrl ,'editor','']),
			objectName:this.objectName,
			extraParams:{
				column:colId
			},
			columnName:colId,
			listeners:{
				'objectsUpdated':{
					fn:function(){
						this.dataGrid.getStore().load();
					},
					scope:this
				},
				'editorRemoved':{
					fn:function(){
						this.dataGrid.getStore().load();
						window.close();
					},
					scope:this
				}
			}
		});
		window.add(properties);
		window.show();
	},
	/**
	 * Add column action
	 */
	addColumn:function(){
		Ext.MessageBox.prompt(appLang.MESSAGE , desLang.enterColumnId,function(btn , text){
			if(btn !='ok'){
				return;
			}

			Ext.Ajax.request({
				url:this.controllerUrl + 'addcolumn',
				method: 'post',
				params:{
					'id':text,
					'object':this.objectName
				},
				scope:this,
				success: function(response, request) {
					response =  Ext.JSON.decode(response.responseText);
					if(response.success){
						this.reload();
					}else{
						Ext.Msg.alert(appLang.MESSAGE, response.msg);
					}
				},
				failure:function() {
					Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
				}
			});
		},this);
	},
	/**
	 * Fill columns using store fields
	 */
	importFieldsFromStore:function()
	{
		var win = Ext.create('designer.grid.exportFieldsWin',{
			controllerUrl:this.controllerUrl,
			objectName:this.objectName
		});
		win.on('allSet',function(){
			this.reload();
			win.close();
		},this);
		win.show();
	},
    showFilterWindow:function(column){
        var win = Ext.create('designer.grid.column.FilterWindow',{
            title:desLang.filter,
            objectName : this.objectName,
            columnId: column,
            controllerUrl:this.controllerUrl
        });
        win.on('filterChange',function(){
            this.reload();
        },this);

        Ext.defer(function () {
            win.show().toFront();
        }, 50);
    }
});