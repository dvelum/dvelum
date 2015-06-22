/**
 *
 * @event dataSaved
 * @param id
 * @param className
 * @param title
 *
 * @event dataSaved
 *
 * @event objectRemoved
 */
Ext.define('designer.objects.Grid',{
	extend:'Ext.Panel',
	layout:'fit',
	dataStore:null,
	dataGrid:null,

	initComponent:function()
	{
		this.dataGrid = Ext.create('Ext.grid.Panel',{
			store:this.dataStore,
			columns:[
				{
					text:desLang.name,
					dataIndex:'title',
					flex:1
				},{
					xtype:'actioncolumn',
					width:30,
					items:[
						{
							iconCls:'deleteIcon',
							title:desLang.remove,
							scope:this,
							handler:this.removeObject
						}
					]
				}
			],
			viewConfig:{
				stripeRows:false
			},
			frame: false,
			loadMask:true,
			columnLines: true,
			scrollable:true,
			listeners : {
				'itemclick':{
					fn:function(view , record , number , event , options){
						this.fireEvent('itemSelected' , record.get('id') , record.get('objClass'), record.get('title'), false);
					},
					scope:this
				}
			}
		});

		this.items = [this.dataGrid];

		this.callParent(arguments);

	},
	getStore:function(){
		return this.dataStore;
	},
	/**
	 * Remove component from project
	 */
	removeObject:function(grid, rowIndex, colIndex)
	{
		var record = grid.getStore().getAt(rowIndex);

		Ext.Ajax.request({
			url:this.controllerUrl + 'remove',
			method: 'post',
			params:{
				'id':record.get('id')
			},
			scope:this,
			success: function(response, request) {
				response =  Ext.JSON.decode(response.responseText);
				if(response.success){
					grid.getStore().remove(record);
					this.fireEvent('objectRemoved');
					this.fireEvent('dataChanged');
				}else{
					Ext.Msg.alert(appLang.MESSAGE, response.msg);
				}
			},
			failure: app.formFailure

		});
	}

});