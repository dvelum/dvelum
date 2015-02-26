Ext.define('app.crud.orm.deploy.Db',{
	extend:'Ext.Panel',
	controllerUrl:null,
	serverId:null,
	title:appLang.DB,
	hideHeaders:true,
	lastSyncLabel:null,
	
	dataGrid:null,
	fataStore:null,
	layout:'fit',
	border:false,
	
	initComponent:function()
	{
		
		this.lastSyncLabel = Ext.create('Ext.toolbar.TextItem',{text:''});
		this.tbar=[
		      {
		    	 text:appLang.SYNC_INFO,
		    	 iconCls:'refreshIcon',
		    	 handler:this.requestServerInfo,
		    	 scope:this
		      },
		     '->',
		     this.lastSyncLabel
		];

		
		this.dataStore = Ext.create('Ext.data.Store', {
		    model: 'app.crud.orm.ObjectsModel',
		    proxy: {
		        type: 'ajax',
		    	url:'index',
		        reader: {
		            type: 'json',
					rootProperty: 'data',
		            idProperty: 'name'
		        },
		        simpleSortMode: true
		    },
		    autoLoad: false,
		    sorters: [{
		                  property : 'name',
		                  direction: 'ASC'
		    }]
		});
		
		this.dataGrid = Ext.create('app.crud.orm.dataGrid',{
			store: this.dataStore,
		    editable:false
		});
				
		this.items = [this.dataGrid];
		this.callParent();
	},

	loadInfo: function(serverId){
		this.serverId = serverId;
		Ext.Ajax.request({
			url: this.controllerUrl + 'db',
			method: 'post',
			scope:this,
			timeout:3600,
			params:{
				server_id:this.serverId
			},
			success: function(response, request) {
				response =  Ext.JSON.decode(response.responseText);
				if(response.success){				
					this.dataStore.loadData(response.data.info);
					this.lastSyncLabel.setText(appLang.LAST_SYNC + ': ' + response.data.date);				
				}else{
					Ext.Msg.alert(appLang.MESSAGE, response.msg);
				}
			},
			failure:function() {
				Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
			}
		});
	},
	/*
	 * Update server info
	 */
	requestServerInfo:function()
	{
		this.getEl().mask(appLang.LOADING);
		Ext.Ajax.request({
			url: this.controllerUrl + 'dbsync',
			method: 'post',
			scope:this,
			timeout:3600000,
			params:{
				server_id:this.serverId
			},
			success: function(response, request) {
				response =  Ext.JSON.decode(response.responseText);
				if(response.success){
					this.loadInfo(this.serverId);
				}else{
					Ext.Msg.alert(appLang.MESSAGE, response.msg);
				}
				this.getEl().unmask();
			},
			failure:function() {
				Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
				this.getEl().unmask();
			}
		});

		if(!app.taskWindow){
			setTimeout(function(){
				var url = app.createUrl([app.admin , 'tasks']);
				app.taskWindow = window.open(url,'taskWindow','width=750,height=400,top=300,left=100,toolbar=0,status=0,menubar=0');
			},500);
		}else{
			setTimeout(function(){
				app.taskWindow.focus();
			},500);
		}
	}
});