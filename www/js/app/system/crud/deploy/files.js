/**
 * @event dataSaved
 *
 */
Ext.define('app.crud.orm.deploy.Files',{
	extend:'Ext.Panel',
	controllerUrl:null,
	serverId:null,
	title:appLang.FILES,
	hideHeaders:true,
	lastSyncLabel:null,

	deletedFiles:null,
	
	createArchiveBtn:null,
	
	layout:'border',
	
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
		     '-',
		     this.lastSyncLabel,
		     '->',
		     {
				text:appLang.CREATE_DEPLOY_PACKAGE,
				scope:this,
				iconCls:'makeIcon',
				handler:this.createPackage
			  }
		];
		
		this.bbar = [
             {
		    	 text:appLang.RELOAD_RESULTS,
		    	 iconCls:'refreshIcon',
		    	 handler:function(){
		    		 this.loadInfo(this.serverId);
		    	 },
		    	 scope:this
		      }
		];		
		
		this.updatedFiles = Ext.create('Ext.tree.Panel',{
			rootVisible:false,
			title:appLang.FILES_TO_UPLOAD,
			region:'center',
			split:true,
	        useArrows: true,
	        flex:1,
	        store:Ext.create('Ext.data.TreeStore',{
				fields:[
				  {name:'id' , type:'string'},
				  {name:'text', type:'string'}
				],
				data:[],
				autoLoad:false,
				root: {
				        text:appLang.ROOT,
				        expanded: true,
				        id:0
				}
			})
		});
		var selModel = Ext.create('Ext.selection.CheckboxModel',{});
		this.deletedFiles = Ext.create('Ext.grid.Panel',{
			title:appLang.FILES_TO_REMOVE,
	        useArrows: true,
	        region:'east',
	        width:400,
	        split:true,
	        flex:1,
	        selModel: selModel,
	        store:Ext.create('Ext.data.Store',{
				proxy: {
				        type: 'ajax',
				    	url:'',
				    	reader: {
				            type: 'json',
				            idProperty: 'id'
				        }
				},
				fields:[
				  {name:'id' , type:'string'}
				],
				autoLoad:false
			}),
			columns:[
			         {text:appLang.FILE,dataIndex:'id',flex:1}
			]
		});
		
		this.items = [this.updatedFiles , this.deletedFiles];
		this.callParent();
				
		this.updatedFiles.on('checkchange',app.checkChildNodes,this);	
		
		//this.deletedFiles.on('checkchange',app.checkChildNodes,this);
	},
	reloadInfo:function(){
		this.loadInfo(this.serverId);
	},
	loadInfo: function(serverId){
		this.serverId = serverId;
		Ext.Ajax.request({
			url: this.controllerUrl + 'files',
			method: 'post',
			scope:this,
			timeout:3600,
			params:{
				server_id:this.serverId
			},
			success: function(response, request) {
				response =  Ext.JSON.decode(response.responseText);
				if(response.success){
					this.setTreeData(this.updatedFiles , response.data.updated);
					this.deletedFiles.getStore().loadData(response.data.deleted);					
					this.lastSyncLabel.setText(appLang.LAST_SYNC + ': ' + response.data.date);				
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

	},
	setTreeData:function(tree , data)
	{	
		
			if(!data.length){
				var newRoot = {
						text:appLang.ROOT,
				        expanded: true,
				        id:0,
				        children:[]
				};
			}else{
				
				var newRoot = {
						text:appLang.ROOT,
				        expanded: true,
				        id:0,
				        children:data
				};
			}
			
			
			tree.setRootNode(newRoot);	
	},
	/*
	 * Update server info
	 */
	requestServerInfo:function()
	{
		this.getEl().mask(appLang.LOADING);
		Ext.Ajax.request({
			url: this.controllerUrl + 'sync',
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
				app.taskWindow = window.open(url,'taskWindow','width=600,height=400,top=300,left=300,toolbar=0,status=0,menubar=0');
			},500);
		}else{
			setTimeout(function(){
				app.taskWindow.focus();
			},500);
		}
	},
	createPackage:function(){
		var filesToUpdate = this.updatedFiles.getChecked('id');
		
		var fileDeleteSM = this.deletedFiles.getSelectionModel();
		
		var filesToDelete = [];
		
		if(fileDeleteSM.hasSelection()){
			var selected = fileDeleteSM.getSelection();
			Ext.each(selected,function(record){
				filesToDelete.push(record.get('id'));
			});
	   }
		
		
		this.getEl().mask(appLang.LOADING);
		Ext.Ajax.request({
			url: this.controllerUrl + 'make',
			method: 'post',
			scope:this,
			timeout:3600000,
			params:{
				'server_id':this.serverId,
				'update_files[]':filesToUpdate,
				'delete_files[]':filesToDelete
			},
			success: function(response, request) {
				response =  Ext.JSON.decode(response.responseText);
				if(response.success){
					this.fireEvent('backupCreated');
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
				app.taskWindow = window.open(url,'taskWindow','width=600,height=400,top=300,left=300,toolbar=0,status=0,menubar=0');
			},500);
		}else{
			setTimeout(function(){
				app.taskWindow.focus();
			},500);
		}
	}
});