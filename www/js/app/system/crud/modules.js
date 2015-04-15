Ext.ns('app.crud.modules');

/**
 * @event completeEdit value
 */
Ext.define('app.crud.modules.interfaceField',{
	extend:'Ext.form.FieldContainer',
	mixins:{completeEdit:'Ext.Editor'},
	alias:'widget.interfaceField',
	triggerCls : 'urlTrigger',
	dataField:null,
	triggerButton:null,
	layout: 'hbox',
	value:'',
	controllerUrl:'',

	initComponent:function(){
		 var  me = this;
		
		 this.dataField = Ext.create('Ext.form.field.Display',{
			flex:3,
			readOnly:true,
			bodyPadding:2,
			listeners:{
				click:{
					fn:me.showSelectorWindow,
					scope:me
				}
			}
		 });
		
		this.triggerButton = Ext.create('Ext.button.Button',{
			 iconCls:'urltriggerIcon',
			 width:25,
			 scope:me,
			 handler:me.showSelectorWindow
		 });
		 this.items = [
		               this.dataField , 
		               this.triggerButton,
		               {
		            	   xtype:'button',
		            	   iconCls:'deleteIcon',
		            	   scope:me,
		            	   handler:function(){
		            		   me.setValue('');
		            		   me.fireEvent('completeEdit' , me.getValue());
		            	   }
		               }
		               ];		 
		 
		 this.callParent(arguments);

		 this.setValue(this.value);
	},
	showSelectorWindow:function()
	{
		var me = this;
		Ext.create('app.filesystemWindow',{
			 title:appLang.SELECT_INTERFACE_PROJECT,
			 viewMode:'select',
			 controllerUrl:app.createUrl([this.controllerUrl ]),
			 listeners:{
				 scope: me,
				 fileSelected:{
					 fn:function(value){
						 me.setValue(value);
						 me.fireEvent('completeEdit', me.getValue());
					 },
					 scope:this
				 }
			 }
		 }).show();
	},
	setValue:function(value){
		this.dataField.setValue(value);
	},
	getValue:function(){
		return this.dataField.getValue();
	},
	reset:function(){
		this.dataField.reset();
	},
	isValid:function(){
		return true;
	}
	 
});


Ext.define('app.crud.modules.Model', {
    extend: 'Ext.data.Model',
    fields: [
         {name:'class',type:'string'},
 	     {name:'dev' , type:'boolean'},
 	     {name:'active', type:'boolean'},	 	        
 	     {name:'title', type:'string'},
 	     {name:'designer',type:'string'},
 	     {name:'in_menu' , type:'boolean'},
 	     {name:'related_files', type:'string'}
 	     
    ],
    validations:[
         {type: 'presence', name: 'class'},    
         {type: 'presence', name: 'title'} 
    ]
});

Ext.define('app.crud.modules.Main',{
    extend:'Ext.Panel',
	dataStore:null,
	controllersStore:null,
	dataGrid:null,
	searchField:null,
	saveButton:null,
	addButton:null,
	
	layout:'fit',
	
	canEdit:false,
	canDelete:false,
	controllerUrl:'',
	

	initComponent: function(){
		var me = this;

		this.controllersStore = Ext.create('Ext.data.Store' , {
		    model:'app.comboStringModel',
		 	autoLoad:true,
		 	autoSave:false,
			proxy:{
				type: 'ajax',
			    api: {
			        read: this.controllerUrl + 'controllers'
			    },
			    reader: {
		            type: 'json',
		            root: 'data',
		            idProperty: 'id'
		        },
		    	simpleSortMode: true
			},
			sorters: [{
                  property : 'title',
                  direction: 'ASC'
            }]
		});
		
		this.cellEditing = Ext.create('Ext.grid.plugin.CellEditing', {clicksToEdit: 1});
		this.dataStore = Ext.create('Ext.data.Store' , {
		    model:'app.crud.modules.Model',
		 	autoLoad:true,
		 	autoSave:false,
			proxy:{
				type: 'ajax',
				url: this.controllerUrl+ 'list',
			    reader: {
		            type: 'json',
		            root: 'data',
		            idProperty: 'class'
		        },
		    	simpleSortMode: true
			},
			sorters: [{
                  property : 'title',
                  direction: 'ASC'
            }]
		});

		
	   var columns = [
						{
							text:appLang.CONTROLLER,
							dataIndex:'class',
							align:'left',
							width:170,
							editor:{
								displayField:"title",
								queryMode:"local",
								triggerAction:"all",
								forceSelection:true,
								valueField:"id",
								allowBlank: false,
								xtype:"combo",
								store:this.controllersStore
							}
						 },{
						    text: appLang.TITLE,
						    dataIndex: 'title',
						    id:'title',
						    width:200,
						    align:'left',
						    editor:{
						    	xtype:'textfield',
						    	allowBlank:false
						    },
						    editable:this.canEdit
						},{
							xtype: 'componentcolumn', 
							text:appLang.INTERFACE,
							dataIndex:'designer',
							width:200,
							flex:1,
							renderer: function(value , meta , record) { 
				                return { 
				                	xtype:'interfaceField',
									controllerUrl:me.controllerUrl,
				                    value: value,    
				                    listeners:{				                    	
										completeEdit:{
											fn:function(value){
												record.set('designer' , value);
											},
											scope:me
										}
									}
				                }; 
				            }
						},{
							text:appLang.DEV,
						    dataIndex: 'dev',
						    align:'center',
						    width:60,
						    xtype:'checkcolumn',
						    renderer:app.checkboxRenderer,
						    editable:this.canEdit
						  },{
							text:appLang.ACTIVE,
						    dataIndex: 'active',
						    width:60,
						    align:'center',
						    id:'active',
						    renderer:app.checkboxRenderer,
						    xtype:'checkcolumn',						  
						    editable:this.canEdit
						},{
							text:appLang.IN_MENU,
						    dataIndex: 'in_menu',
						    width:60,
						    align:'center',
						    id:'in_menu',
						    renderer:app.checkboxRenderer,
						    xtype:'checkcolumn',						  
						    editable:this.canEdit
						}   
	   ];
		
	   if(this.canEdit){
		   columns.push(
			 { 
			   xtype:'actioncolumn',
	    	   width:30,
	    	   align:'center',
			   items:[
			       {
			    	   iconCls:'deleteIcon',
			    	   tooltip:appLang.DELETE,
			    	   width:30,
			    	   scope:this,
			    	   handler:function(grid , row , col){
			    		   var store = grid.getStore();
			    		   this.removeModule(store.getAt(row));
			    	   }
			       }
			 	]
	       });
	   }
	   
		
	   this.dataGrid = Ext.create('Ext.grid.Panel',{
				  store: this.dataStore,
				  viewConfig:{
					  stripeRows:true
				  },
				  frame: false,
			      loadMask:true,
				  columnLines: true,
				  autoScroll:true,
				  selModel: {
			          selType: 'cellmodel'
			      },
			      columns: columns,
			      plugins: [this.cellEditing],
			      tbar:[{
						iconCls:'newdocIcon',
						hidden:!this.canEdit,
						text:appLang.ADD_ITEM,
						scope:this,
						handler:this.addAction
					  },{
						iconCls:'saveIcon',
						hidden:!this.canEdit,
						text:appLang.SAVE,
						scope:this,
						handler:this.saveAction
			      },'-',{
			    	  	iconCls:'newdocIcon',
						hidden:!this.canEdit,
						text:appLang.CREATE_MODULE,
						scope:this,
						handler:this.createAction
					  }]
	
	   });
	   
	  this.items = [this.dataGrid];
	  this.callParent(arguments); 
	  
   },
   saveAction:function(){
	   var valid = true;
	   this.dataStore.each(function(record){
		   if(!record.isValid()){
			   valid = false;
		   }
	   },this);
	   
	   if(!valid){
		   Ext.Msg.alert(appLang.MESSAGE, appLang.INVALID_VALUE);
		   return;
	   }
	   
	   var data = app.collectStoreData(this.dataStore);
	   Ext.Ajax.request({
	 		url: app.root + "update",
	 		method: 'post',
	 		params:{
	 			'data':Ext.JSON.encode(data)
	 		},
	 		scope:this,
	        success: function(response, request) {
	 			response =  Ext.JSON.decode(response.responseText);
	 			if(response.success){
	 				this.dataStore.load();
	 			}else{
	 				Ext.Msg.alert(appLang.MESSAGE, response.msg);
	 			}	
	       },
	       failure:function() {
			   Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
	       }
	 	});
   },
    hasDirtyRecords:function(){
    	var has = false;
    	this.dataStore.each(function(record)
    	{
    		if(record.dirty || record.phantom)
    		{
    			has = true;
    		}
    	},this);
    	
		return has;
    },
   addAction:function(){	
	   var r = Ext.create('app.crud.modules.Model', {
           name: '',
           dev: 0,
           active: 0,
           title:'',
           in_menu:true
       });
	   r.setDirty();
       this.dataStore.insert(0, r);
       this.cellEditing.startEditByPosition({row: 0, column: 0});
   },
   createAction:function(){
   		if(this.hasDirtyRecords()){
   	   		Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_SAVE_MODULES_BEFORE_CREATE);
   	   		return;
   	 	}
   	 	
	   var win = Ext.create('app.crud.modules.CreateWindow',{
		   controllerUrl:this.controllerUrl,
		   title:appLang.NEW_MODULE
	   });
	   
	   win.on('dataSaved',function(data){
		   this.controllersStore.load();
		   var r = Ext.create('app.crud.modules.Model', {
			   'class':data['class'],
	           'name':data.name,
	           'dev': data.dev,
	           'active':data.active,
	           'title':data.title,
	           'designer':data.designer,
	           'in_menu':true
	       });
		   r.setDirty();
	       this.dataStore.insert(0, r);
	       this.saveAction();
	   },this);
	   
	   win.show();
   },
   removeModule:function(record){
	   var me = this;

	   if(!record.get('class') || record.get('class')=='')
	   {
		   me.dataStore.remove(record);
		   return;
	   }
	   
	   if(this.hasDirtyRecords()){
  	   		Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_SAVE_CHANGES_BEFORE_DELETE);
  	   		return;
  	   }
	   var win = Ext.create('app.crud.modules.DeleteWindow',{
		  moduleId:record.get('id'),
		  controllerUrl:this.controllerUrl,
		  title:appLang.REMOVE_MODULE + ' "' + record.get('title')+'"',
		  relatedFiles:record.get('related_files')
	   });
	   
	   win.on('deleteItems' , function(deleteRelated){
		   Ext.Ajax.request({
			    url: this.controllerUrl + 'deletemodule',
				method: 'post',
				params:{
					id:record.get('id'),
					delete_related:deleteRelated
			    },				
		        success: function(response, request) {
					response =  Ext.JSON.decode(response.responseText);
					if(response.success){										 
						me.dataStore.remove(record);
						me.dataStore.commitChanges();
						win.close();
					}else{
						Ext.Msg.alert(appLang.MESSAGE, response.msg);
					}	
		      },
		      failure: function(){
		    	  Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
		      }
		});
	   },this);
	   
	   win.show(); 
   }
});

/**
 * @event dataSaved
 * @params data
 */
Ext.define('app.crud.modules.CreateWindow',{
	extend:'Ext.Window',
	modal:true,
	resizable:false,
	width:400,
	height:120,
	layout:'fit',
	
	dataForm:null,
	controllerUrl:'',
	
	
	initComponent:function(){
		
		this.dataForm = Ext.create('Ext.form.Panel',{
			bodyCls:'formBody',
			bodyPadding:5,
			fieldDefaults:{
				labelAlign:'right',
				labelWidth:90,
				anchor:'100%'
			},
			items:[
			      {
			      	  xtype:'combobox',
			    	  name:'object',
			    	  fieldLabel:appLang.OBJECT,
			    	  queryMode:'local',
			    	  valueField:'id',
			    	  forceSelection:true,
			    	  displayField:'title',
			    	  allowBlank:false,
			    	  store:Ext.create('Ext.data.Store',{
			    		  model:'app.comboStringModel',
			    		  proxy: {
				  		        type: 'ajax',
				  		    	url:this.controllerUrl + 'objects',
				  		        reader: {
				  		            type: 'json',
				  		            root: 'data',
				  		            idProperty: 'id'
				  		        },
				  		        simpleSortMode: true
				  		    },
				  		    autoLoad: true,
				  		    sorters: [
				  		              {
				  		                  property : 'title',
				  		                  direction: 'ASC'
				  		              }
				  		    ]
			    	  })
					}
			]
		});
		
		this.buttons = [
		  {
			  text:appLang.CANCEL,
			  scope:this,
			  handler:this.close
		  },{
			  text:appLang.CREATE,
			  scope:this,
			  handler:this.createModule
		  }             
		];
		this.items = [this.dataForm];
		this.callParent();
	},	
	createModule:function()
	{
		var handle = this;
		
		this.dataForm.getForm().submit({
			clientValidation: true,
			waitMsg:appLang.SAVING,
			method:'post',
			url:this.controllerUrl + 'create',
			success: function(form, action) {	
   		 		if(!action.result.success){
   		 			Ext.Msg.alert(appLang.MESSAGE, action.result.msg);
   		 		} else{
   		 			handle.fireEvent('dataSaved' , action.result.data);		 
   		 			handle.close();
   		 		}
   	        },
   	        failure: app.formFailure
   	    });
	}
});
/**
 * @event dataSaved
 * @params boolean deleteRelated
 */
Ext.define('app.crud.modules.DeleteWindow',{
	extend:'Ext.Window',
	modal:true,
	width:400,
	height:200,
	moduleId:false,
	controllerUrl:false,
	relatedFiles:false,
	layout:'fit',
	
	initComponent:function(){
		this.removeFiles = Ext.create('Ext.form.field.Checkbox',{
			name:'remove_related',
			boxLabel:appLang.REMOVE_MODULE_FILES +':',
			value:false
		});
		
		this.items = [
		    {
		      xtype:'form',
		      bodyCls:'formBody',
		      bodyPadding:2,
			  items:[  
			    this.removeFiles,
			    {
			    	xtype:'displayfield',
			    	value:this.relatedFiles
			    }
			  ]
		    }
		];
		
		this.buttons = [
		    {
		    	text:appLang.DELETE,
		    	scope:this,
		    	handler:function(){
		    		this.fireEvent('deleteItems', this.removeFiles.getValue());
		    	}
		    },{
		    	text:appLang.CANCEL,
		    	scope:this,
		    	handler:function(){
		    		this.close();
		    	}
		    }
		];
		this.callParent();
	}
});


Ext.onReady(function(){
	
	var dataPanel = Ext.create('app.crud.modules.Main',{
		title:appLang.MODULES + ' :: ' + appLang.HOME,
		canEdit:canEdit,
		canDelete:canDelete,
		controllerUrl:app.root
	});
	
	app.content.add(dataPanel);	
});
