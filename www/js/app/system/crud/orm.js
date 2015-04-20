Ext.ns('app.crud.orm');

app.crud.orm.canEdit = false;
app.crud.orm.canDelete = false;
app.crud.orm.foreignKeys = false;

app.crud.orm.oList = [];

app.crud.orm.intTypes = ['tinyint','smallint','mediumint','int','bigint', 'boolean'];
app.crud.orm.floatTypes = ['decimal' ,'float' ,'double'];

app.crud.orm.charTypes = ['char','varchar'];
app.crud.orm.textTypes = ['tinytext','text','mediumtext','longtext'];
app.crud.orm.dateTypes = ['date','datetime','time','timestamp'];
app.crud.orm.blobTypes = ['tinyblob','blob','mediumblob','longblob'];
app.crud.orm.Actions = {};



Ext.define('app.crud.orm.Field', {
    extend: 'Ext.data.Model',
    fields: [
        {name:'name' ,  type:'string'},
        {name:'title' , type:'string'},
        {name:'required', type:'boolean'},	 	        
        {name:'db_len', type:'integer'},
        {name:'db_isNull', type:'boolean'},
        {name:'db_default',type:'string'},
        {name:'is_search',type:'boolean'},
        {name:'system', type:'boolean'},
        {name:'type' , type:'string'},
        {name:'unique', type:'boolean'},
        {name:'link_type', type:'string'},
        {name:'object',type:'string'},
        {name:'broken', type:'boolean'}
    ]
});


Ext.define('app.crud.orm.Index', {
    extend: 'Ext.data.Model',
    fields: [
        {name:'name' ,  type:'string'},
        {name:'fulltext' , type:'boolean'},
        {name:'unique', type:'boolean'},	 	        
        {name:'columns', type:'string'},
        {name:'primary', type:'boolean'}
    ]
});

Ext.define('app.crud.orm.Main',{
	extend:'Ext.panel.Panel',
	dataStore:null,
	dataGrid:null,
	searchField:null,
	toolbarDataGrid:null,
	controllerUrl:'',
	layout:'fit',

    initComponent:function(){
    	this.tbar = [];
    	this.dataStore = Ext.create('Ext.data.Store', {
		    model: 'app.crud.orm.ObjectsModel',
		    proxy: {
		        type: 'ajax',
		    	url:app.crud.orm.Actions.listObj,
		        reader: {
		            type: 'json',
		            root: 'data',
		            idProperty: 'name'
		        },
		        actionMethods : {
		    		create : 'POST',
		    		read   : 'POST',
		    		update : 'POST',
		    		destroy: 'POST'
		    	},
		        simpleSortMode: true
		    },
		    autoLoad: true,
		    sorters: [
		              {
		                  property : 'name',
		                  direction: 'ASC'
		              }
		    ],
		    listeners:{
		    	scope:this,
		    	load:function(store, records){
		    		app.crud.orm.oList = [];
		    		Ext.each(records, function(record)
		    		{
		    			 var title = record.get('title');
		    			 if(title.length)
		    				 title=' ('+title+')';
		    			 
		    			 app.crud.orm.oList.push({id:record.get('name'),title:(record.get('name')+title)});
		    		},this);
		    	}
		    }
		});
		
		if(app.crud.orm.canEdit){
			this.tbar = [{
				text:appLang.ADD_OBJECT,
				tooltip:appLang.ADD_OBJECT,
				listeners:{
					click:{
						fn:function(){
							this.showEdit(null);
						},
						scope:this
					}
				}
			},{
				text:appLang.DICTIONARIES,
				tooltip:appLang.TOOLTIP_DICTIONARIES,
				listeners:{
					click:{
						fn:function(){
							this.showDictionary(false);
						},
						scope:this
					}
				}
			},{
				text:appLang.SHOW_OBJECTS_MAP,
				tooltip:appLang.TOOLTIP_OBJECTS_MAP,
				handler:this.showObjectsMap,
				scope:this
			},{
				text:appLang.DB_CONNECTIONS,
				handler:this.showConnections,
				scope:this
			},{
				text: appLang.DB_CONNECT_EXTERNAL,
				handler:this.importToOrm,
				scope:this
			},{
				text:appLang.SHOW_LOG,
				tooltip:appLang.BUILDER_SHOW_LOG,
				handler:this.showLog,
				scope:this
			}
			];
		}

		this.searchField = new SearchPanel({store:this.dataStore,fieldNames:['name' , 'table'],local:true});

	   	this.toolbarDataGrid = Ext.create('Ext.toolbar.Toolbar', {
		   	items:[this.searchField,'-',{
	            xtype:'button',
	   	    	text:appLang.BUILD_ALL,
	   	    	tooltip:appLang.BUILD_ALL,
	   	    	icon:'/i/system/build.png',
	   	    	scope:this,
	   	    	handler:this.rebuildAllObjects
	   	    }]
	   	});
  	 
	   /*	if(app.crud.orm.canEdit && app.crud.orm.canUseBackup)
	   	{
		   	this.toolbarDataGrid.add({
		   			text:appLang.MAKE_BACKUP,
		   			tooltip:appLang.MSG_MAKE_BACKUP_ALL,
		   			icon:'/i/system/database-export.png',
		   			scope:this,
		   			handler:this.makeBackUp
		   	});
	   		
	   		this.toolbarDataGrid.add({
	   			text:appLang.RESTORE_BACKUP,
	   			tooltip:appLang.RESTORE_BACKUP_TOOLTIP,
	   			icon:'/i/system/database-import.png',
	   			scope:this,
	   			handler:this.restoreBackUp
	   		});
	   	}
	   	*/
	   	this.toolbarDataGrid.add('-', ' ', {
	   		xtype: 'fieldcontainer',
	   		items:[{
		   		xtype:'checkboxfield',
	            listeners:{
	            	scope:this,
	            	change:function(field, value){
	            		if(value){
	            			this.dataStore.filter("system",false);
	            		}else{
	            			this.dataStore.clearFilter();
	            		}
	            	}
	            }
		   	}]
	   	},appLang.HIDE_SYSTEM_OBJ);
	   	
	   	var handle = this;
	   	this.dataGrid = Ext.create('app.crud.orm.dataGrid',{
			store: this.dataStore,
		    tbar:this.toolbarDataGrid,
		    loadMask:true,
		    editable:true,
		    listeners:{
		    	'itemdblclick':{
		    		fn:function(view , record , number , event , options){
		    			 this.showEdit(record);
		    		},
		    		scope:this
		    	},
		    	'editRecord':{
		    		fn:this.showEdit,
		    		scope:this
		    	},
		    	'rebuildTable':{
		    		fn:this.rebuildObject,
		    		scope:this
		    	},
		    	'removeItem':{
		    		fn:this.removeObject,
		    		scope:this
		    	},
		    	'viewData':{
		    		fn:this.showDataView,
		    		scope:this
		    	}
		    }
		});
	 this.items = [this.dataGrid];
	 this.callParent();
   },
   	removeObject:function(grid, rowIndex, colIndex){
   		var store = grid.getStore();
   		var rec = store.getAt(rowIndex);
   		
   		if(rec.get('external')){
   			Ext.Msg.alert(appLang.MESSAGE , appLang.MSG_CANT_DELETE_EXTERNAL_OBJECT);
   			return;
   		}
   		
   		Ext.Msg.confirm(appLang.CONFIRM, appLang.MSG_OBJECT_REMOVE, function(btn){
   			if(btn != 'yes'){
   				return false;
   			}
   			
	   		Ext.getBody().mask(appLang.MSG_OBJECT_REMOVING);
			Ext.Ajax.request({
				url: app.crud.orm.Actions.removeObject,
			   	method: 'post',
			   	scope:this,
			   	params:{
			 		'objectName':store.getAt(rowIndex).get('name')
			 	},
			   	success: function(response, request) {
					response =  Ext.JSON.decode(response.responseText);
				   	if(response.success){
				   		store.removeAt(rowIndex);
				   	} else {
					   	Ext.Msg.alert(appLang.MESSAGE , response.msg);
				   	}
				   	Ext.getBody().unmask();
			   	},
			   	failure:function() {
				   	Ext.Msg.alert(appLang.MESSAGE , appLang.MSG_LOST_CONNECTION);
				   	Ext.getBody().unmask();
			   	}
		   });
   		}, this);
   	},
   restoreBackUp:function(){
	   app.crud.orm.restoreWin = Ext.create('app.crud.orm.restoreBackupWindow',{});
	   app.crud.orm.restoreWin.on('backupRestored',function(){
		   this.dataStore.load();
	   },this);
	   app.crud.orm.restoreWin.show();
   },
   makeBackUp:function(){
	   Ext.getBody().mask(appLang.MSG_BACKUP_PROCESSING);
	   Ext.Ajax.request({
		   url: app.crud.orm.Actions.makeBackUp,
		   method: 'post',
		   scope:this,
		   timeout:3600000,
		   success: function(response, request) {
			   response =  Ext.JSON.decode(response.responseText);
			   if(!response.success){
				   Ext.Msg.alert(appLang.MESSAGE , response.msg);
			   }
			   if(!Ext.isEmpty(app.crud.orm.restoreWin)){
				   app.crud.orm.restoreWin.dataGrid.getStore().load();
			   }
			   Ext.getBody().unmask();
		   },
		   failure:function() {
			   Ext.Msg.alert(appLang.MESSAGE , appLang.MSG_LOST_CONNECTION);
			   Ext.getBody().unmask();
		   }
	   });
   },
   rebuildAllObjects:function(){
	   Ext.Msg.confirm(appLang.CONFIRMATION, appLang.MSG_CONFIRM_REBUILD, function(btn){
		   if(btn != 'yes')
			   return;
		   
		   var handle = this;
		   var oNamesList = [];
		   this.dataStore.each(function(record){
			   oNamesList.push(record.get('name'));
		   },this);
		    
		   this.dataGrid.getEl().mask(appLang.SAVING);
		   Ext.Ajax.request({
		 		url: app.crud.orm.Actions.buildAllObjects,
		 		method: 'post',
		 		scope:this,
		 		timeout:3600000,
		 		params:{
		 			'names[]':oNamesList
		 		},
		         success: function(response, request) {
		        	 this.dataGrid.getEl().unmask();
		 			response =  Ext.JSON.decode(response.responseText);
		 			if(response.success){
		 				this.dataStore.load();
		 			}else{
		 				Ext.Msg.alert(appLang.MESSAGE , response.msg);
		 			}
		       },
		       failure:function() {
		    	   this.dataGrid.getEl().unmask();
		    	   Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);  
		       }
		   });
	   }, this);
   },
   showEdit:function(record){
	   
	 var oName = Ext.isEmpty(record) ? '' : record.get('name');  
	 
	 var win = Ext.create('app.crud.orm.ObjectWindow',{
		 objectName:oName,
		 objectList:app.crud.orm.oList,
		 isSystem:Ext.isEmpty(record) ? false : record.get('system'),
		 isExternal:Ext.isEmpty(record) ? false : record.get('external')
	 }); 
	 win.setTitle(appLang.EDIT_OBJECT + ' &laquo;' + oName + '&raquo; ');
	 win.on('dataSaved',function(){
		this.dataStore.load();
	 },this);
	 win.on('showdictionarywin',function(name){
	 	this.showDictionary(name);
	 },this);
	 win.on('fieldRemoved',function(){
		 this.dataStore.load();
	 },this);
	 win.on('indexRemoved',function(){
		 this.dataStore.load();
	 },this);
	 win.show();
   },
   /**
    * Show Dictionary editor window
    */
   showDictionary:function(name){
		Ext.create('app.crud.orm.DictionaryWindow', {
			curDictionary:name,
			controllerUrl:app.crud.orm.Actions.dictionary,
			canEdit:app.crud.orm.canEdit,
			canDelete:app.crud.orm.canDelete
		}).show();  
   },
   /**
    * Show Objects Uml
    */
   showObjectsMap:function(){
		Ext.create('app.crud.orm.ObjectsMapWindow',{
			controllerUrl:this.controllerUrl,
			canEdit:app.crud.orm.canEdit 
		}).show();
   },
   /**
    * Rebuild all DB Objects
    */
   rebuildObject:function(name)
   {
	   var handle = this;
	   this.win = Ext.create('Ext.Window',{
		 width:400,
		 height:500,
		 modal:true,
		 autoScroll:true,
		 layout:'fit',
		 title:appLang.REBUILD_INFO,
		 closeAction:'destroy',
		 buttons:[
		      {
		    	  text:appLang.CANCEL,
		    	  scope:handle,
		    	  handler:function(){
		    		  this.win.close();
		    	  }
		      },{
		    	  text:appLang.APPLY,
		    	  scope:handle,
		    	  handler:function(){
		    		  this.buildObject(name);
		    		  this.win.close();
		    	  }
		      }   
		 ]
	   });
	   this.win.show();
	   this.win.setLoading(appLang.FETCHING_INFO);

	   Ext.Ajax.request({
	 		url: app.crud.orm.Actions.validateObject,
	 		method: 'post',
	 		params:{
	 			'name':name
	 		},
	 		scope:this,
	 		timeout:3600000,
	        success: function(response, request) {
	 			response =  Ext.JSON.decode(response.responseText);
	 			if(response.success){
	 				if(response.nothingToDo){
	 					Ext.Msg.alert(appLang.MESSAGE, appLang.NTD);
	 					handle.win.close();
	 				}else{
	 					handle.win.add({
		 					xtype:'panel',
		 					autoScroll:true,
		 					html:response.text,
		 					bodyPadding:3		 					
		 				});
	 				}
	 			}else{
	 				Ext.Msg.alert(appLang.MESSAGE , appLang.CANT_GET_VALIDATE_INFO);
	 			}	
	 			handle.win.setLoading(false);
	       },
	       failure:function() {
	       	Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);   
	       	handle.win.setLoading(false);
	       }
	   });
   },
   /**
    * Build Db Object
    * @param string name
    */
   buildObject:function(name){
	   var handle = this;
	   Ext.Ajax.request({
	 		url: app.crud.orm.Actions.buildObject,
	 		method: 'post',
	 		params:{
	 			'name':name
	 		},
	 		timeout:3600000,
	         success: function(response, request) {
	 			response =  Ext.JSON.decode(response.responseText);
	 			if(response.success){
	 				handle.dataStore.load();
	 			}else{
	 				Ext.Msg.alert('Error' , response.msg);
	 			}	
	       },
	       failure:app.formFailure 
	 	});
   },
   /**
    * Show database building log
    * for currentdevelopment version
    */
   showLog:function(){
	   var handle = this;
	   Ext.Ajax.request({
	 		url: app.crud.orm.Actions.builderLog,
	 		method: 'post',
	         success: function(response, request) {
	 			response =  Ext.JSON.decode(response.responseText);
	 			if(response.success){
	 				Ext.create('Ext.Window',{
	 					width:600,
	 					height:600,
	 					closeAction:'destroy',
	 					layout:'fit',
	 					title:appLang.LOG,
	 					bodyPadding:5,
	 					autoScroll:true,
	 					bodyStyle:{
	 						backgroundColor:'#ffffff'
	 					},
	 					html:response.data
	 				}).show();
	 			}else{
	 				Ext.Msg.alert('Error' , response.msg);
	 			}	
	       },
	       failure:app.formFailure 
	 	});
    },
    /**
     * Show data view window 
     * @param string objectName
     * @param string objectTitle
     */
    showDataView:function(record)
    {
    	var win = Ext.create('app.crud.orm.DataViewWindow',{
    		objectName:record.get('name'),
    		title:record.get('title'),
    		isVc:record.get('vc'),
    		controllerUrl:app.crud.orm.Actions.dataViewController
    	});
    	
    	win.show();
    },
    showConnections:function()
    {
    	Ext.create('app.orm.connections.Window',{
    		dbConfigs:app.crud.orm.dbConfigs,
    		controllerUrl:app.crud.orm.Actions.connectionsUrl
    	}).show();
    },
    /**
     * Show Import window
     */
    importToOrm:function(){
    	Ext.create('app.orm.import.Window',{
    		dbConfigs:app.crud.orm.dbConfigs,
    		controllerUrl:app.crud.orm.Actions.importUrl,
    		listeners:{
    			'importComplete':{
    				fn:function(){
    					this.dataGrid.getStore().load();
    				},
    				scope:this
    			}
    		}
    	}).show();
    }
});

Ext.onReady(function(){
	
	app.crud.orm.Actions = {
			addDictionary:		app.root + 'adddictionary',
			listDictionaries:	app.root + 'listdictionaries',
			updateDictionary:	app.root + 'updatedictionary',
			removeDictionary:	app.root + 'removedictionary',
			listObj: 			app.root + 'list',
			listObjFields: 		app.root + 'fields',
			listObjIndexes: 	app.root + 'indexes',
			listBackups: 		app.root + 'listbackups',
			loadObjCfg: 		app.root + 'load',
			loadObjField: 		app.root + 'loadfield',
			loadObjIndex: 		app.root + 'loadindex',
			makeBackUp: 		app.root + 'makebackup',
			removeBackUp:		app.root + 'removebackup',
			removeObject:		app.root + 'removeobject',
			restoreBackup: 		app.root + 'restorebackup',
			saveObjCfg: 		app.root + 'save',
			saveObjField: 		app.root + 'savefield',
			saveObjIndex:	 	app.root + 'saveindex',
			deleteIndex: 		app.root + 'deleteindex',
			deleteField:	 	app.root + 'deletefield',
			validateObject: 	app.root + 'validate',
			buildObject:		app.root + 'build',
			buildAllObjects:	app.root + 'buildall',
			builderLog:			app.root + 'log',
			dictionary:			app.createUrl([app.root+'dictionary','']),
			listValidators:		app.createUrl([app.root+'listvalidators','']),
			dataViewController: app.createUrl([app.root+'dataview','']),
			connectionsUrl:		app.createUrl([app.root+'connections','']),
			listConnections:	app.createUrl([app.root+'connectionslist','']),
			importUrl:			app.createUrl([app.root+'import','']),
			encryptData:		app.root + 'encryptdata',
			decryptData:		app.root + 'decryptdata'
	};
	app.crud.orm.dbConfigs = dbConfigsList;
	app.crud.orm.canEdit = canEdit;
	app.crud.orm.canDelete = canDelete;
	app.crud.orm.canUseBackup = canUseBackup;
	app.crud.orm.foreignKeys = useForeignKeys;
	
	var dataPanel = Ext.create('app.crud.orm.Main',{
		title:appLang.MODULE_ORM,
		controllerUrl:app.root
	});
	
	app.content.add(dataPanel);
});

