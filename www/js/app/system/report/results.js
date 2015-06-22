
Ext.define('app.report.Results',{
	extend:'Ext.Panel',
	dataGrid:null,
	dataStore:null,
	layout:'fit',

	initComponent:function(){

		this.tbar = [
			{
				iconCls:'refreshIcon',
				tooltip:appLang.RELOAD,
				handler:this.loadResult,
				scope:this
			},
			{
				iconCls:'csvIcon',
				tooltip:appLang.EXPORT_TO_CSV,
				handler:this.exportCSV,
				scope:this
			}
		];

		this.callParent(arguments);
	},

	loadResult:function(){
		var me = this;
		Ext.Ajax.request({
			url: app.root + "results",
			method: 'post',
			success: function(response, request) {
				response =  Ext.JSON.decode(response.responseText);
				me.removeAll();

				if(!response.success)
					return;

				me.dataStore = Ext.create('Ext.data.Store', {
					fields:response.data.fields,
					proxy: {
						type: 'ajax',
						url: app.root+ 'data',
						reader: {
							type: 'json',
							rootProperty: 'data',
							totalProperty: 'count',
							idProperty: 'id'
						},
						startParam:'pager[start]',
						limitParam:'pager[limit]',
						sortParam:'pager[sort]',
						directionParam:'pager[dir]',
						extraParams:{
						},
						simpleSortMode: true
					},
					pageSize: 200,
					remoteSort: true,
					autoLoad: true
				});

				me.dataGrid = Ext.create('Ext.grid.Panel',{
					store: me.dataStore,
					viewConfig: {
						stripeRows: true
					},
					frame: false,
					loadMask:true,
					columnLines: true,
					scrollable:true,
					bodyBorder:false,
					border:false,
					columns: response.data.columns,
					bbar: Ext.create('Ext.PagingToolbar', {
						store: me.dataStore,
						displayInfo: true,
						displayMsg: appLang.DISPLAYING_RECORDS +' {0} - {1} '+appLang.OF+' {2}',
						emptyMsg:appLang.NO_RECORDS_TO_DISPLAY
					})
				});
				me.add(me.dataGrid);
				//me.dataGrid.on('columnmove',me.onColMove , me);

			},
			failure:function() {
				Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
			}
		});
	},/*,
	 onColeMove:function(ct, column, fromIdx, toIdx, eOpts ){
	 var cols = ct.getGridColumns();
	 Ext.each(cols,function(item){
	 console.log(item);
	 },this);
	 }
	 */
	exportCSV:function(){
		window.location = app.root+ "exportcsv";
	}
});