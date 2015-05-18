Ext.define('app.FilesystemTree',{
	extend:'Ext.tree.Panel',
	controllerUrl:'',
	listAction: 'fslist',
	folderSort:true,
	
	constructor:function(config){
		config = Ext.apply({
	        rootVisible:true,
	        useArrows: true
	    }, config || {});				
        this.callParent(arguments);
	},
	
	initComponent:function(){
		this.store = Ext.create('Ext.data.TreeStore',{
			folderSort:true,
			fields:[
			  {name:'id' , type:'string'},
			  {name:'text' , type:'string'},
			  {name:'url' , type:'string'}
			],
			proxy: {
			        type: 'ajax',
			    	url:this.controllerUrl + this.listAction,
			    	reader: {
			            type: 'json',
			            idProperty: 'id'
			        }
			},
			autoLoad:false,
			root: {
				text: '/',
				expanded: true,
				id:'/'
			}
		});
		this.callParent();
		this.on('show',function(){app.checkSize(this);});
	}
	
});

/**
 *
 * @event fileSelected
 * @param string path
 *
 * @event fileCreated
 * @param string path
 *
 */
Ext.define('app.filesystemWindow',{
	
	extend:'Ext.Window',
	width:300,
	height:500,
	layout:'fit',
	
	fileTree:null,
	itemForm:null,
	
	listAction:'fslist',
	
	controllerUrl:'',
	/**
	 * View mode
	 * @property string   - select / create
	 */
	viewMode:'select',
	
	createExtension:'',
	
	initComponent:function(){

		this.fileTree = Ext.create('app.FilesystemTree',{
			controllerUrl:this.controllerUrl,
	    	listAction:this.listAction,
			split: true,
			tbar:[
			      {
			    	  text:appLang.CREATE_DIR,
			    	  scope:this,
			    	  handler:this.makeDir,
			    	  hidden:(this.viewMode == 'select')?true:false
			      }
			]
		});	
			
		this.items = [this.fileTree];	
		
		this.buttons = [];
			
		switch(this.viewMode){
			case 'select' : this.buttons.push({text:appLang.SELECT,handler:this.onSelectClick,scope:this});
				break;
			case 'create' : this.buttons.push({text:appLang.CREATE,handler:this.onCreateClick,scope:this});
				break;	
		}
				
		this.buttons.push({
			text:appLang.CLOSE,
			handler:this.close,
			scope:this
		});
		
		this.callParent(arguments);
		this.on('show',function(){app.checkSize(this);});
	},
	makeDir:function(){
		 var me = this;
		 
		 var sm = this.fileTree.getSelectionModel();
		 var path = '';
		 
		 Ext.MessageBox.prompt(appLang.MESSAGE, appLang.ENTER_DIR_NAME, function(btn,text){
			 if(btn!='ok' || text.length<1){
				 return;
			 }
			 
			 var item = null;
			 if(sm.hasSelection()){
				 item = sm.getSelection()[0];
				 if(!item.get('leaf')){
					path = item.get('id');
				 }
			 }
			 Ext.Ajax.request({
				 	url:me.controllerUrl + 'fsmakedir',
			 		method: 'post',
			 		params:{'path':path,'name':text},
			        success: function(response, request) {
			 			response =  Ext.JSON.decode(response.responseText);
			 			if(response.success){
			 				//if(item!=null){
			 				//	item.getLoader().load();
			 				//}else{
			 					me.fileTree.getStore().load();
			 				//}
			 				
			 			}else{
			 				Ext.Msg.alert(appLang.MESSAGE, response.msg);   	
			 			}
			       },
			       failure:function() {
			       		Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);   	
			       }
			 });
		 });
	},
	onSelectClick:function(){
		var sm = this.fileTree.getSelectionModel();
		
		if(!sm.hasSelection()){
			Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_SELECT_FILE);   
			return;
		}
		
		var item = sm.getSelection()[0];
		
		if(!item.get('leaf')){
			Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_SELECT_FILE);   
			return;
		}
		this.fireEvent('fileSelected' , item.get('id') , item);
		this.close();
	},
	onCreateClick:function(){
		var sm = this.fileTree.getSelectionModel();
		var me = this;
		
		if(!sm.hasSelection()){
			Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_SELECT_DIR);   
			return;
		}
		
		Ext.MessageBox.prompt(appLang.MESSAGE, appLang.ENTER_FILE_NAME, function(btn,text){
			 if(btn!='ok' || text.length<1){
				 return;
			 }
			 
			 var item = null;
			 if(sm.hasSelection()){
				 item = sm.getSelection()[0];
				 if(!item.get('leaf')){
					path = item.get('id');
				 }
			 }
			 			 
			 Ext.Ajax.request({
				 	url:me.controllerUrl + 'fsmakefile',
			 		method: 'post',
			 		params:{'path':path,'name':text},
			        success: function(response, request) {
			 			response =  Ext.JSON.decode(response.responseText);
			 			if(response.success){	 				
			 				me.fireEvent('fileCreated' , response.data.file);
			 				me.close();
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