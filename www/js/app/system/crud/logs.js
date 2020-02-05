Ext.ns('app.crud.logs');

app.crud.logs.Actions = [];

Ext.define('app.crud.logs.Model', {
    extend: 'Ext.data.Model',
    fields: [
     	{name:'id' , type:'integer'},
        {name:'user' ,type:'string'},
        {name:'date', type:'date' , dateFormat:'Y-m-d H:i:s'},
        {name:'type',type:'string'},
        {name:'record_id', type:'date' , type:'integer'},
        {name:'table_name',type:'string' }
    ]
});

Ext.define('app.crud.logs.Main',{
	   extend:'Ext.panel.Panel',
	  /**
	    * @property {Ext.grid.Panel}
	    */
	   dataGrid:null,
	   /**
	    * @property {Ext.data.Store}
	    */
	   dataStore:null,
	   /**
	    * @property {Ext.form.field.ComboBox}
	    */
	   operationFilter:null,

	   initComponent:function(){
		   this.layout = 'fit';

		   this.dataStore = Ext.create('Ext.data.Store', {
			    model: 'app.crud.logs.Model',
			    proxy: {
			        type: 'ajax',
			    	url:app.root +  'list',
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
				    simpleSortMode: true
			    },
			    pageSize: 80,
		        remoteSort: true,
			    autoLoad: true,
			    sorters: [{
	                  property : 'date',
	                  direction: 'DESC'
	            }]
			});


		   this.operationFilter = Ext.create('Ext.form.field.ComboBox',{
			   store:Ext.create('Ext.data.Store',{
				   model:app.comboModel,
				   data:app.crud.logs.Actions,
	    		   remoteSort:false
			   }),
			   queryMode:'local',
			   valueField:'id',
			   displayField:'title',
			   value:0,
			   listeners:{
				   select:{
					   fn:function(cmp){
						   this.dataStore.proxy.setExtraParam('filter[type]' , cmp.getValue());
						   this.dataStore.load();
					   },
					   scope:this
				   }
			   }
		   });

			this.dataGrid = Ext.create('Ext.grid.Panel',{
					store: this.dataStore,
				 	viewConfig:{
				 		stripeRows:false
				 	},
		            frame: false,
		            loadMask:true,
				    columnLines: true,
					scrollable:true,
				    tbar:[
				      appLang.ACTION + ': ',this.operationFilter
				    ],
				    columns: [
							{
								text:appLang.DATE,
								 dataIndex:'date',
					        	 sortable:true,
					        	 width:100,
					        	 xtype:'datecolumn',
					        	 format:'d.m.Y H:i'
							},
							{
								 sortable: true,
								 text: appLang.ACTION,
								 dataIndex: 'type',
								 width:90
							},{
							    sortable: true,
							    text: appLang.DATA_TABLE,
							    dataIndex: 'table_name',
							    width:130
							},{
							    sortable: true,
							    text: appLang.RECORD_ID,
							    dataIndex: 'record_id',
							    width:90
							},{
							    sortable: true,
							    text: appLang.USER,
							    dataIndex: 'user',
							    width:130
							}
				    ],
					bbar: Ext.create('Ext.PagingToolbar', {
			            store: this.dataStore,
			            displayInfo: true,
			            displayMsg: appLang.DISPLAYING_RECORDS+' {0} - {1} ' + appLang.OF + ' {2}',
			            emptyMsg:appLang.NO_RECORDS_TO_DISPLAY
			        })
			});
		   this.items=[this.dataGrid];
		   this.callParent(arguments);
	   }
});



Ext.onReady(function(){
	Ext.QuickTips.init();
	app.crud.logs.Actions = logActions;
	app.content.add(
			Ext.create('app.crud.logs.Main',{
				title:appLang.HISTORY_LOG + ' :: '+ appLang.HOME
			})
	);
});