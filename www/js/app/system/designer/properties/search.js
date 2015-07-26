/**
 * Properties panel for Search Component
 */
Ext.define('designer.properties.Search',{

	extend:'designer.properties.Field',

	initComponent:function()
	{
		var me = this;
		this.sourceConfig = Ext.apply({
			'fieldNames':{
				editor: Ext.create('Ext.form.field.Text',{
					listeners:{
						'focus':{
							fn:me.namesEdior,
							scope:me
						}
					}
				}),
				renderer:function(v){return '...';}
			}
		} , this.sourceConfig);

		this.callParent();
	},
	namesEdior:function(){
		var storeProperty = this.dataGrid.getSource().store;
		var fieldsList = this.dataGrid.getSource().fieldNames;

		if(fieldsList.length){
			fieldsList = Ext.JSON.decode(fieldsList);
		}else{
			fieldsList = [];
		}

		if(!storeProperty.length){
			Ext.Msg.alert(appLang.MESSAGE, desLang.selectDataStore);
			return;
		}

		Ext.create('designer.properties.SearchFieldsWindow',{
			fieldsStore: storeProperty,
			initData : fieldsList,
			listeners:{
				dataChanged:{
					fn:function(data){
						this.dataGrid.setProperty('fieldNames' , data);
						this.fireEvent('dataChanged');
					},
					scope:this
				}
			}
		}).show();
	}
});

/**
 *
 * @event dataChanged
 */
Ext.define('designer.properties.SearchFieldsWindow',{

	extend:'Ext.Window',

	width:200,
	height:300,
	/*
	 * window title as property name
	 */
	title:'fieldNames',

	initData:null,
	dataGrid:null,
	dataStore:null,
	fieldsStore:null,
	closeAction:'destroy',
	resizable:false,
	layout:'fit',

	storeName:'',

	initComponent:function(){

		if(Ext.isEmpty(this.initData)){
			this.initData = [];
		}else{
			var data = [];
			Ext.each(this.initData ,function(item){
				data.push([item]);
			},this);
			this.initData = data;
		}

		this.dataStore =  Ext.create('Ext.data.ArrayStore',{
			fields: ['id'],
			data: this.initData
		}),

			this.fieldStore = Ext.create('Ext.data.Store',{
				proxy: {
					type: 'ajax',
					url:app.createUrl([designer.controllerUrl ,'store','listfields']),
					reader: {
						type: 'json',
						idProperty: 'name',
						rootProperty: 'data'
					},
					extraParams:{
						object:this.fieldsStore
					},
					autoLoad:true
				},
				fields: [
					{name:'name' ,  type:'string'},
					{name:'type' ,  type:'string'}
				],
				autoLoad:true
			});

		this.tbar=[
			{
				tooltip:desLang.add,
				iconCls:'plusIcon',
				scope:this,
				handler:this.addRecord
			}
		];
		this.cellEditing = Ext.create('Ext.grid.plugin.CellEditing', {clicksToEdit:1});
		this.dataGrid = Ext.create('Ext.grid.Panel',{
			store:this.dataStore,
			columnLines:true,
			hideHeaders:true,
			columns:[
				{
					dataIndex:'id',
					editable:true,
					flex:1,
					editor:{
						xtype: 'combobox',
						typeAhead: true,
						triggerAction: 'all',
						selectOnTab: true,
						forceSelection:true,
						store:this.fieldStore,
						displayField:'name',
						valueField:'name',
						allowBlank:false,
						queryMode:'local'
					}
				},{
					xtype:'actioncolumn',
					width:25,
					align:'center',
					sortable: false,
					menuDisabled:true,
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
			plugins:[this.cellEditing]
		});

		this.items = [this.dataGrid];

		this.buttons = [
			{
				text:desLang.save,
				handler: this.sendData,
				scope:this
			},{
				text:desLang.close,
				handler:this.close,
				scope:this
			}
		];
		this.callParent();
	},
	sendData:function()
	{
		var data = [];
		this.dataStore.each(function(record){
			var name =  record.get('id');
			if(name.length){
				data.push(record.get('id'));
			}
		},this);

		data = Ext.JSON.encode(data);

		this.fireEvent('dataChanged' , data);
		this.close();
	},
	addRecord:function(){
		var count = this.dataStore.getCount();
		var r = [''];
		this.dataStore.insert(count, r);
		this.cellEditing.startEditByPosition({row: count, column: 0});
	}
});
