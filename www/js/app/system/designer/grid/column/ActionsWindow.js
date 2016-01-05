Ext.define('designer.grid.column.ActionsWindow',{
	extend:'Ext.Window',
	layout:'border',


	width:app.checkWidth(700),
	height:app.checkHeight(500),
	modal:true,

	objectName:null,
	columnId:null,
	controllerUrl:null,

	dataGrid:null,
	dataStore:null,

	propertiesPanel:null,

	initComponent:function()
	{
		this.dataStore = Ext.create('Ext.data.Store',{
			proxy: {
				type: 'ajax',
				url:this.controllerUrl+'itemslist',
				reader: {
					type: 'json',
					idProperty: 'id',
					rootProperty:'data'
				},
				extraParams:{
					object:this.objectName,
					column:this.columnId
				}
			},
			fields: [
				{name:'id' ,  type:'string'},
				{name:'tooltip' , type:'string'},
				{name:'icon',type:'string'}
			],
			autoLoad:true,
			sorters: [{
				property : 'order',
				direction: 'ASC'
			}]
		});
		var me = this;
		var bufferedSave = Ext.Function.createBuffered(me.saveOrder,1200, me);

		this.dataGrid = Ext.create('Ext.grid.Panel',{
			store:this.dataStore,
			region:'center',
			split:true,
			columnLines:true,
			tbar:[
				{
					text:desLang.add,
					scope:this,
					handler:this.addActionItem
				}
			],
			columns:[
				{
					dataIndex:'icon',
					renderer:function(v){
						if(v.length){
							return '<img src="'+v+'"/>';
						}
					},
					width:50,
					align:'center',
					text:desLang.icon,
					sortable:false
				}, {
					dataIndex:'tooltip',
					text:desLang.tooltip,
					flex:1,
					sortable:false
				},
				{
					xtype:'actioncolumn',
					width:60,
					tooltip:appLang.SORT,
					dataIndex:'id',
					sortable:false,
					items:[
						{
							iconCls: 'downIcon',
							handler:function(grid, rowIndex, colIndex){
								var total = grid.getStore().getCount();
								if(rowIndex == total - 1)
									return;

								var sRec = grid.getStore().getAt(rowIndex);
								grid.getStore().removeAt(rowIndex);
								grid.getStore().insert(rowIndex+1 , sRec);
								bufferedSave();

							}
						},{
							iconCls: 'upIcon',
							handler:function(grid, rowIndex, colIndex){
								var total = grid.getStore().getCount();
								if(rowIndex == 0)
									return;

								var sRec = grid.getStore().getAt(rowIndex);
								grid.getStore().removeAt(rowIndex);
								grid.getStore().insert(rowIndex -1 , sRec);
								bufferedSave();
							}
						},{
							iconCls:'deleteIcon',
							tooltip:desLang.removeAction,
							scope:this,
							handler:function(grid, rowIndex, colIndex){
								var rec = grid.getStore().getAt(rowIndex);
								this.removeAction(rec);
							}
						}
					]
				}
			]
		});


		this.propertiesPanel =  Ext.create('designer.properties.GridColumn',{
			autoLoadData:false,
			controllerUrl: app.createUrl([designer.controllerUrl ,'gridcolumnactions','']),
			eventsControllerUrl:app.createUrl([designer.controllerUrl ,'gridcolumnactionevents','']),
			extraParams:{
				column:this.columnId
			},
			objectName:this.objectName,
			width:380,
			listeners:{
				dataSaved:{
					fn:function(){},
					scope:this
				}
			},
			title:desLang.properties,
			layout:'fit',
			region:'east',
			split:true,
			width:250
		});


		this.propertiesPanel.dataGrid.on('propertychange',function(source, recordId, value){
			switch(recordId){
				case 'tooltip':
					var sm = this.dataGrid.getSelectionModel();
					if(sm.hasSelection()){
						var record = sm.getSelection()[0];
						record.beginEdit();
						record.set(recordId, value);
						record.endEdit();
						record.commit();
					}
					break;
				case 'icon':
					this.dataStore.load();
					break;
				default: return;
			}
		},this);

		this.dataGrid.getSelectionModel().on('selectionchange',function(sm , data , opts){
			if(!sm.hasSelection()){
				this.propertiesPanel.resetProperties();
				return;
			}
			this.propertiesPanel.setExtraParams({'id':sm.getSelection()[0].get('id')});
			this.propertiesPanel.loadProperties();
			this.propertiesPanel.refreshEvents();
		},this);

		this.items = [this.dataGrid , this.propertiesPanel];
		this.callParent();
	},
	/**
	 * Save items order
	 */
	saveOrder:function(){
		var order = [];
		this.dataStore.each(function(record){
			order.push(record.get('id'));
		},this);

		Ext.Ajax.request({
			url:this.controllerUrl + 'sortactions',
			method: 'post',
			scope:this,
			params:{
				'object':this.objectName,
				'column':this.columnId,
				'order[]':order
			},
			success: function(response, request) {
				response =  Ext.JSON.decode(response.responseText);
				if(!response.success){
					Ext.Msg.alert(appLang.MESSAGE, response.msg);
				}else{
					designer.msg(desLang.success , desLang.sortSaved);
				}
			},
			failure:function() {
				Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
			}
		});
	},
	addActionItem:function()
	{
		Ext.Msg.prompt(appLang.MESSAGE , desLang.enterActionName , function(btn , text){
			if(btn !='ok'){
				return;
			}

			Ext.Ajax.request({
				url:this.controllerUrl + 'addaction',
				method: 'post',
				scope:this,
				params:{
					'name':text,
					'object':this.objectName,
					'column':this.columnId
				},
				success: function(response, request) {
					response =  Ext.JSON.decode(response.responseText);
					if(response.success){
						this.dataStore.load();
						designer.msg(desLang.success , desLang.actionAdded);
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
	removeAction:function(record)
	{
		var me = this;
		Ext.MessageBox.confirm(appLang.CONFIRMATION , desLang.removeAction + ' "' + record.get('text')+'"',function(btn){
			if(btn !='yes'){
				return;
			}

			Ext.Ajax.request({
				url:me.controllerUrl + 'removeaction',
				method: 'post',
				params:{
					'name':record.get('id'),
					'object':me.objectName,
					'column':me.columnId
				},
				success: function(response, request) {
					response =  Ext.JSON.decode(response.responseText);
					if(response.success){
						me.dataStore.remove(record);
						designer.msg(desLang.success , desLang.actionRemoved);
					}else{
						Ext.Msg.alert(appLang.MESSAGE, response.msg);
					}
				},
				failure:function() {
					Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
				}
			});

		});
	}
});