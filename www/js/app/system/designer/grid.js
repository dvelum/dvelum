
Ext.ns('designer.grid','designer.grid.column');

/**
 *
 * @event allSet
 * @param {Ext.Window}
 *
 */
Ext.define('designer.grid.exportFieldsWin',{
	extend:'Ext.Window',

	layout:'fit',
	title:desLang.importStore,
	width:300,
	height:250,
	modal:true,

	controllerUrl:null,
	objectName:null,

	dataGrid:null,
	dataStore:null,

	initComponent:function(){
		this.dataStore = Ext.create('Ext.data.Store',{
			model:'designer.model.fieldsModel',
			proxy: {
				type: 'ajax',
				url:app.createUrl([designer.controllerUrl ,'store','']) +  'allStoreFields',
				reader: {
					type: 'json',
					rootProperty: 'data',
					idProperty: 'id'
				},
				extraParams:{
					object:this.objectName
				},
				simpleSortMode: true
			},
			autoLoad:true
		});

		this.dataGrid = Ext.create('Ext.grid.Panel',{
			border:false,
			store:this.dataStore,
			selModel:Ext.create('Ext.selection.CheckboxModel'),
			columns:[{
				text:desLang.field,
				dataIndex:'name',
				flex:1
			},{
				text:desLang.type,
				dataIndex:'type',
				width:85
			}]
		});

		this.items = [this.dataGrid];

		this.buttons = [{
			text:desLang.select,
			scope:this,
			handler:this.addFields
		},{
			text:desLang.cancel,
			scope:this,
			handler:this.close
		}];
		this.callParent(arguments);
	},
	addFields:function(){
		records = this.dataGrid.getSelectionModel().getSelection();
		var col = [];
		Ext.each(records,function(record){
			col.push({name:record.get('name'), type:record.get('type')});
		},this);
		Ext.Ajax.request({
			url:this.controllerUrl + 'addcolumns',
			method: 'post',
			params:{
				'col':Ext.encode(col),
				'object':this.objectName
			},
			scope:this,
			success: function(response, request) {
				response =  Ext.JSON.decode(response.responseText);
				if(response.success){
					this.fireEvent('allSet');
				}else{
					Ext.Msg.alert(appLang.MESSAGE, response.msg);
				}
			},
			failure:function() {
				Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
			}
		});
	},
    destroy: function () {
        this.dataStore.destroy();
        this.dataGrid.destroy();
        this.callParent(arguments);
    }
});


Ext.define('designer.grid.filterOptionsModel', {
	extend: 'Ext.data.Model',
	fields: [
		{name:'value',type:'string'}
	]
});
/**
 *
 * @event dataChanged
 * Fires after data successfuly saved
 * @param string propertyName
 * @param string json_encoded object of grid source
 * @param boolean True to create the property if it doesn't already exist. Defaults to false.
 *
 */
Ext.define('designer.grid.filterOptionsWindow',{
	extend:'Ext.Window',
	width:500,
	height:300,
	layout:'fit',
	modal:true,
	title:desLang.items,
	dataGrid:null,
	dataStore:null,
	initialData:[],
	cellEditing:null,
	objectName:null,
	controllerUrl:null,

	initComponent:function(){

		this.tbar=[
			{
				tooltip:desLang.add,
				iconCls:'plusIcon',
				scope:this,
				handler:this.addRecord
			}
		];

		this.dataStore = Ext.create('Ext.data.Store', {
			autoDestroy: true,
			model:'designer.grid.filterOptionsModel',
			data:this.initialData,
			autoLoad:false
		});

		this.cellEditing = Ext.create('Ext.grid.plugin.CellEditing', {
			clicksToEdit: 1
		});

		this.dataGrid = Ext.create('Ext.grid.Panel',{
			columnLines: true,
			autoHeight:true,
			store:this.dataStore,
			columns:[
				{
					text:desLang.value,
					dataIndex:'value',
					flex:1,
					editor: {
						xtype: 'textfield'
					}
				},{
					xtype:'actioncolumn',
					width:25,
					sortable: false,
					menuDisabled:true,
					align:'center',
					items:[
						{
							iconCls:'deleteIcon',
							tooltip:desLang.remove,
							handler:function(grid , row , col){
								var store = grid.getStore();
								store.remove(store.getAt(row));
							}
						}
					]
				}
			],
			plugins :[this.cellEditing]
		});

		this.items = [this.dataGrid];
		this.buttons = [
			{
				text:desLang.save,
				handler:this.saveData,
				scope:this
			},{
				text:desLang.close,
				handler:function(){
					this.close();
				},
				scope:this
			}
		];
		this.callParent(arguments);
	},
	addRecord:function(){
		var count = this.dataStore.getCount();
		var r = Ext.create('designer.grid.filterOptionsModel', {
			name:''
		});
		this.dataStore.insert(count, r);
		this.cellEditing.startEditByPosition({row: count, column: 0});
	},
	saveData:function(){
		this.dataStore.commitChanges();
		var options = [];
		this.dataStore.each(function(record){
			options.push(record.get('value'));
		});
		this.fireEvent('dataChanged', 'options', Ext.encode(options), true);
		this.close();
	},
    destroy: function () {
        this.dataStore.destroy();
        this.dataGrid.destroy();
        this.cellEditing.destroy();
        this.callParent(arguments);
    }
});
