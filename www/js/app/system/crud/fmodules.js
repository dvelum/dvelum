Ext.ns('app.crud.fmodules');

Ext.define('app.crud.fmodules.Model', {
    extend: 'Ext.data.Model',
    fields: [
         {name:'name', type:'string'}, 
         {name:'class', type:'string'}, 	 
 	     {name:'title', type:'string'},     
    ],
    validations:[
         {type: 'presence', name: 'name'},    
         {type: 'presence', name: 'class'}, 
         {type: 'presence', name: 'title'} 
    ]
});

Ext.define('app.crud.fmodules.Main',{
    extend:'Ext.Panel',
	dataStore:null,
	controllersStore:null,
	dataGrid:null,
	searchField:null,
	saveButton:null,
	addButton:null,
	
	canEdit:false,
	canDelete:false,
	controllerUrl:'',
	
	layout:'fit',
	
	initComponent: function()
	{
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
					rootProperty: 'data',
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
		    model:'app.crud.fmodules.Model',
		 	autoLoad:true,
		 	autoSave:false,
			proxy:{
				type: 'ajax',
				url: this.controllerUrl + 'list',
			    reader: {
		            type: 'json',
					rootProperty: 'data',
		            idProperty: 'name'
		        },
		    	simpleSortMode: true
			},
			sorters: [{
                  property : 'title',
                  direction: 'ASC'
            }]
		});

	   var classEditor = Ext.create('Ext.form.field.ComboBox',{
		   	displayField:"title",
			queryMode:"local",
			triggerAction:"all",
			forceSelection:true,
			valueField:"id",
			allowBlank: false,
			name:"main_topic",
			xtype:"combo",
			store:this.controllersStore
	   });
		
		
	   var columns = [
	                  {
							text: appLang.CODE,
							dataIndex: 'name',
							width:250,
							align:'left',
							editor:{
							   xtype:'textfield',
							   allowBlank:false,
							   vType:'alpha'
							},
							editable:this.canEdit	
			            },
						{
							text:appLang.CONTROLLER,
							dataIndex:'class',
							align:'left',
							width:250,
							editor:classEditor
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
						}
	   ];
	   
	   
	   if(this.canEdit){
		   columns.push( {
			   xtype:'actioncolumn',
	    	   width:30,
	    	   align:'center',
			   items:[
			         {
			    	   iconCls:'deleteIcon',
			    	   tooltip:appLang.DELETE,
			    	   scope:this,
			    	   width:30,
			    	   handler:function(grid , row , col){
			    		   var store = grid.getStore();
			    		   store.remove(store.getAt(row));
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
					}, {
						iconCls:'saveIcon',
						hidden:!this.canEdit,
						text:appLang.SAVE,
						scope:this,
						handler:this.saveAction
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
	   
	   this.getEl().mask(appLang.SAVING);
	   Ext.Ajax.request({
	 		url: this.controllerUrl + "update",
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
	 			this.getEl().unmask();
	       },
	       failure:function() {
			   Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
			   this.getEl().unmask();
	       }
	 	});
   },
   addAction:function(){
	   var r = Ext.create('app.crud.fmodules.Model', {
           'name': '',
           'class':'',
           'title':''
       });
	   r.setDirty();
       this.dataStore.insert(0, r);
       this.cellEditing.startEditByPosition({row: 0, column: 0});
   }
});


Ext.onReady(function(){
	
	var dataPanel = Ext.create('app.crud.fmodules.Main',{
		title:appLang.FRONTEND_MODULES + ' :: ' + appLang.HOME,
		canEdit:canEdit,
		canDelete:canDelete,
		controllerUrl:app.root 
	});
	
	app.content.add(dataPanel);	
});
