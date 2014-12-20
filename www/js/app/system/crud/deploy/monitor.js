Ext.define('app.crud.orm.deploy.Monitor',{
	extend:'Ext.Panel',
	controllerUrl:'',
	title:'.',
	hidden:true,
	historyPanel:null,
	filesPanel:null,
	dbPanel:null,
	layout:'border',
	border:false,
	/**
	 * Init server interface
	 * @param serverId
	 * @param serverName
	 */
	initServerView:function(serverId , serverName){
		this.serverId = serverId;
		this.setTitle(appLang.SERVER+': ' + serverName);
		this.show();
					
		this.historyPanel.loadInfo(serverId);	
		this.filesPanel.loadInfo(serverId);
		this.dbPanel.loadInfo(serverId);
	},
	
	
	initComponent:function(){
		
		this.historyPanel = Ext.create('app.crud.orm.deploy.History',{
			controllerUrl:this.controllerUrl,
			region:'east',
			width:200,
			split:true,
			collapsible:true
		});
				
		this.filesPanel = Ext.create('app.crud.orm.deploy.Files',{
			split:true,
			controllerUrl:this.controllerUrl 
		});
		
		this.filesPanel.on('backupCreated',function(){
			this.historyPanel.reloadInfo();
		},this);
		
		
		this.dbPanel = Ext.create('app.crud.orm.deploy.Db',{
			split:true,
			controllerUrl:this.controllerUrl 
		});
		
		
		this.items = [
		              {
							region:'center',
							split:true,
							xtype:'tabpanel',
							deferredRender:false,
							items:[
							       
							       this.filesPanel ,
							       this.dbPanel
							]
					  },
		              this.historyPanel       
		];
		
		this.callParent();
	}
});