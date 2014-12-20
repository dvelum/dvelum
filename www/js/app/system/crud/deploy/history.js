Ext.define('app.crud.orm.deploy.History',{
	extend:'Ext.grid.Panel',
	controllerUrl:null,
	serverId:null,
	title:appLang.DEPLOY_HISTORY,
	hideHeaders:true,

	initComponent:function(){

		this.store = Ext.create("Ext.data.Store",{
			autoLoad:false,
			model:'app.comboStringModel',
			proxy:{
				type:"ajax",
				simpleSortMode:true,
				extraParams:{serverId:''},
				url:this.controllerUrl + 'history',
				reader:{
					idProperty:"id",
					root:"data"
				}
			}
		});

		this.columns = [{
		    dataIndex:'title',
		   // text:appLang.DATE,
		    flex:1
		}];

		this.callParent();
	},
	loadInfo: function(serverId){
		this.serverId = serverId;
		this.getStore().proxy.setExtraParam('server_id',this.serverId);
		this.getStore().load();
	},
	reloadInfo:function(){
		this.loadInfo(this.serverId);
	}
});