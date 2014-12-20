Ext.ns('app.crud.orm.deploy');

Ext.define('app.crud.orm.deploy.Application',{
	
	extend:'Ext.Panel',
	layout:'border',
	controllerUrl:'',
	border:false,
	canEdit:false,
	canDelete:false,
	lastSyncLabel:null,
	
	initComponent:function(){
		
		this.lastSyncLabel = Ext.create('Ext.toolbar.TextItem',{text:appLang.LAST_SYNC_ETALON + ': '});
		
		this.tbar=[
		     {
		    	 text:appLang.REFRESH_ETALON,
		    	 iconCls:'refreshIcon',
		    	 handler:this.refreshEtalon,
		    	 scope:this
		      },
		     '-',
		     this.lastSyncLabel
		];
		
		this.serversPanel = Ext.create('app.crud.orm.deploy.Servers',{
			region:'west',
			split:true,
			collapsible:true,
			width:250,
			canEdit:this.canEdit,
			canDelete:this.canDelete,
			controllerUrl:app.createUrl([this.controllerUrl + 'servers',[]]),
			title:appLang.SERVERS
		});
		
		this.viewPanel = Ext.create('app.crud.orm.deploy.Monitor',{
			region:'center',
			split:true,
			canEdit:this.canEdit,
			canDelete:this.canDelete,
			controllerUrl:app.createUrl([this.controllerUrl + 'view',''])
		});
		
		this.items = [this.serversPanel , this.viewPanel];
		
		
		this.serversPanel.on('serverSelected',function(serverId , serverName){
			this.viewPanel.initServerView(serverId, serverName);
		},this);
		
		this.callParent();
		this.loadInfo();
		
	},
	loadInfo: function(serverId){
		Ext.Ajax.request({
			url: this.controllerUrl + 'lastimprint',
			method: 'post',
			scope:this,
			success: function(response, request) {
				response =  Ext.JSON.decode(response.responseText);
				if(response.success){				
					this.lastSyncLabel.setText(appLang.LAST_SYNC_ETALON + ': ' + response.data.date);				
				}else{
					Ext.Msg.alert(appLang.MESSAGE, response.msg);
				}
			},
			failure:function() {
				Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
			}
		});

	},
	refreshEtalon:function(){		
		this.lastSyncLabel.getEl().setHTML('<img src="'+app.wwwRoot+'i/ajaxload.gif" width="14"/> ' + appLang.PROCESSING );
		Ext.Ajax.request({
			url: this.controllerUrl + 'imprint',
			method: 'post',
			scope:this,
			timeout:3600000,
			success: function(response, request) {
				response =  Ext.JSON.decode(response.responseText);
				if(response.success){		
					this.lastSyncLabel.setLoading(false);
					this.lastSyncLabel.setText(appLang.LAST_SYNC_ETALON + ': ' + response.data.date);				
				}else{
					this.lastSyncLabel.setText('...');
					Ext.Msg.alert(appLang.MESSAGE, response.msg);
				}
			},
			failure:function() {
				this.lastSyncLabel.setText('...');
				Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
			}
		});
	}
});