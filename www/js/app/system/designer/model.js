Ext.ns('designer.model');

Ext.define('designer.model.fieldsModel', {
    extend: 'Ext.data.Model',
    fields: [
		{name:'name',type:'string'},
		{name:'type',type:'string'}
    ],
	idProperty:'name'
});
Ext.define('designer.model.associationsModel', {
    extend: 'Ext.data.Model',
    fields: [
		{name:'field',type:'string'},
		{name:'model',type:'string'},
		{name:'type',type:'string'}
	],
	idProperty:'field'
});

Ext.define('designer.model.validationsModel', {
    extend: 'Ext.data.Model',
    fields: [
    	{name:'field',type:'string'},
    	{name:'type',type:'string'}
    ],
	idProperty:'field'
});

/**
 * Model configuration Window
 *
 * @event dataChanged - Fires after data successfuly saved
 * @param string json_encoded object of fields grid data
 * @param string json_encoded object of association grid data
 * @param string json_encoded object of validation grid data
 *
 */
Ext.define('designer.model.configWindow',{
	extend:'Ext.Window',
	width:600,
	height:600,
	layout:'fit',
	tabPanel:null,
	activeTab:0,
	modal:true,
	title:desLang.modelConfig,
	
	objectName:null,
	controllerUrl:null,
	
	fieldsGrid:null,
	fieldsStore:null,
	
	initFields:[],
	initAssociations:[],
	initValidators:[],
	
	fieldsCellEditing:null,
	
	
	initFieldsEditor:function(){
		
		var me = this;
		
		this.fieldsStore = Ext.create('Ext.data.Store', {
			autoDestroy: true,
			model:'designer.model.fieldsModel',
		    autoLoad:true,
		    proxy:{
		    	type:'ajax',	    	
		    	url:app.createUrl([designer.controllerUrl ,'model','listfields']),
		    	extraParams:{
			        object:this.objectName
			    },
		    	reader:{
		    		idProperty:"name",
					rootProperty:"data",
		    		type:"json"
		    	}
		    }
		});
		
		this.fieldsProperties =  Ext.create('designer.properties.dataField',{
			controllerUrl: app.createUrl([designer.controllerUrl ,'datafield','']),
			objectName:this.objectName,
			autoLoadData:false,
			autoLoad:false,
			title:desLang.properties,
			layout:'fit',
			region:'east',
			showEvents:false,
			split:true,
			width:250
		});
				
		this.fieldsCellEditing = Ext.create('Ext.grid.plugin.CellEditing',{clicksToEdit:1});
		
		this.fieldsGrid = Ext.create('Ext.grid.Panel', {
			region:'center',
			store:this.fieldsStore,
			columnLines: true,
			columns:[
			         {
			        	 text:desLang.field,
			        	 dataIndex:'name',
			        	 flex:1,
			        	 editor: {
			                 xtype: 'textfield',	
				        	 vtype:'alphanum',
			                 allowBlank:false
			             }
			         },{
			        	 text:desLang.type,
			        	 dataIndex:'type',
			        	 flex:1,
			        	 editor: {
			                 xtype: 'combobox',
			                 typeAhead: true,
			                 selectOnFocus:true,
							 editable:true,
					   	     triggerAction: 'all',
					   	     anchor:'100%',
				   	   	     queryMode: 'local',
				   	   	     forceSelection:true,
				   	   	     displayField:'id',
				   	   	     valueField:'id',
			                 store: Ext.create('Ext.data.ArrayStore',{
							        fields: ['id'],
							        data: this.fieldsProperties.fieldtypes
						     })
			             }
			         },{
			    			xtype:'actioncolumn',
			    			width:25,
			    			sortable: false,
			    			menuDisabled:true,
			    			align:'center',
			    			items:[
			    			       {
			    			    	   iconCls:'deleteIcon',
			    			    	   tooltip:desLang.remove,
			    			    	   scope:me,
			    			    	   handler:function(grid , row , col){			    			    		  
			    			    		   var store = grid.getStore(); 
			    			    		   me.removeField(store.getAt(row));
			    			    	   }
			    			       }
			    			]
			    	}
			],
			plugins :[this.fieldsCellEditing],
			tbar:[{
				iconCls:'importOrmIcon',
				tooltip:desLang.importOrm,
				scope:this,
				handler:this.importFromOrm
			},{
				iconCls:'importDbIcon',
				tooltip:desLang.importDb,
				scope:this,
				handler:this.importFromDb
			},{
				tooltip:desLang.add,
				iconCls:'plusIcon',
				scope:this,
				handler:this.addField
			}],
			listeners:{
				scope:this,
				edit:function(editor , o){					
					this.fieldsProperties.dataGrid.setProperty(o.field , o.value);					
					this.fieldsStore.commitChanges();
					if(o.field === 'name'){
						this.fieldsProperties.setExtraParams({'id': o.value});
					}
				}
			}
		});
		
		this.fieldsGrid.getSelectionModel().on('selectionchange',function(sm , data , opts){
			this.fieldsCellEditing.cancelEdit();
			if(!sm.hasSelection()){
				this.fieldsProperties.resetProperties();
				this.fieldsProperties.refreshEvents();
				return;
			}
			this.fieldsProperties.setExtraParams({'id':sm.getSelection()[0].get('name')});
			this.fieldsProperties.loadProperties();		
		},this);
			
		this.fieldsProperties.dataGrid.on('propertychange',function(source, recordId, value){			
			if(recordId === 'name' || recordId === 'type'){
				var sm = this.fieldsGrid.getSelectionModel();
				if(sm.hasSelection()){
					var record = sm.getLastSelected();
					record.beginEdit();
					record.set(recordId, value);
					record.endEdit();
					record.commit();
					this.fireEvent('dataChanged');
				}
			}
		},this);
		
		this.fieldsEditor =  Ext.create('Ext.Panel',{
			title:desLang.fields,
			layout:'border',
			frame:false,
			items:[
			      this.fieldsGrid , this.fieldsProperties
			]
		});
		
		return this.fieldsEditor;
	},
	
	initComponent:function(){
				
		var me = this;
		this.initFieldsEditor();
		
		this.associationsGrid = Ext.create('Ext.grid.Panel', {
			title:desLang.associations,
			store:Ext.create('Ext.data.Store', {
				autoDestroy: true,
				model:'designer.model.associationsModel',
			    autoLoad:false,
			    data:this.initAssociations
			}),
			columns:[
			         {
			        	 text:desLang.field,
			        	 dataIndex:'field',
			        	 flex:1,
			        	 editor: {
			                 xtype: 'combobox',
			                 typeAhead: true,
			                 triggerAction: 'all',
			                 queryMode: 'local',
			                 selectOnTab: true,
			                 valueField:'name',
			                 displayField:'name',
			                 forceSelection:true,
			                 store:me.fieldsStore
			             }
			         },
			         {
			        	 text:desLang.type,
			        	 dataIndex:'type',
			        	 flex:1,
			        	 editor: {
			                 xtype: 'combobox',
			                 typeAhead: true,
			                 triggerAction: 'all',
			                 selectOnTab: true,
			                 forceSelection:true,
			                 store: [
			                 	['hasMany','Has Many'],
			                    ['belongsTo','Belongs To']
			                 ]
			             }
			         },{
			        	 text:desLang.model,
			        	 dataIndex:'model',
			        	 flex:1,
			        	 editor: {
			                 xtype: 'combobox',
			                 typeAhead: true,
			                 triggerAction: 'all',
			                 queryMode: 'local',
			                 selectOnTab: true,
			                 valueField:'id',
			                 displayField:'id',
			                 forceSelection:true,
			                 store:app.designer.getModelsStore()
			             }
			         },{
			    			xtype:'actioncolumn',
			    			width:25,
			    			align:'center',
			    			sortable: false,
			    			menuDisabled:true,
			    			items:[
			    			       {
			    			    	   iconCls:'deleteIcon',
			    			    	   tooltip:desLang.remove,
			    			    	   handler:function(grid , row , col){
			    			    		   var store = grid.getStore();
			    			    		   store.remove(store.getAt(row));
			    			    	   }
			    			       }
			    			]
			    	}
			],
			plugins :[Ext.create('Ext.grid.plugin.CellEditing',{clicksToEdit:1})],
			tbar:[{
				tooltip:desLang.add,
				iconCls:'plusIcon',
				scope:this,
				handler:this.addAssociation
			}]
		});
		
		this.validationsGrid = Ext.create('Ext.grid.Panel', {
			title:desLang.validators,
			store:Ext.create('Ext.data.Store', {
				autoDestroy: true,
				model:'designer.model.validationsModel',
			    autoLoad:false,
			    data:this.initValidators
			}),
			columns:[
			         {
			        	 text:desLang.field,
			        	 dataIndex:'field',
			        	 flex:1,
			        	 editor: {
			                 xtype: 'combobox',
			                 typeAhead: true,
			                 triggerAction: 'all',
			                 queryMode: 'local',
			                 selectOnTab: true,
			                 valueField:'name',
			                 displayField:'name',
			                 forceSelection:true,
			                 store:this.fieldsStore
			             }
			         },{
			        	 text:desLang.type,
			        	 dataIndex:'type',
			        	 flex:1,
			        	 editor: {
			        		 xtype: 'combobox',
			                 typeAhead: true,
			                 triggerAction: 'all',
			                 selectOnTab: true,
			                 forceSelection:true,
			                 store: [
			                 	['alpha','alpha'],
			                    ['alphanum','alphanum'],
			                    ['email' , 'email'],
			                    ['url', 'url']
			                 ]
			             }
			         },{
			    			xtype:'actioncolumn',
			    			width:25,
			    			align:'center',
			    			sortable: false,
			    			menuDisabled:true,
			    			items:[
			    			       {
			    			    	   iconCls:'deleteIcon',
			    			    	   tooltip:desLang.remove,
			    			    	   handler:function(grid , row , col){
			    			    		   var store = grid.getStore();
			    			    		   store.remove(store.getAt(row));
			    			    	   }
			    			       }
			    			]
			    	}
			],
			plugins :[Ext.create('Ext.grid.plugin.CellEditing',{clicksToEdit:1})],
			tbar:[{
				tooltip:desLang.add,
				iconCls:'plusIcon',
				scope:this,
				handler:this.addValidation
			}]
		});
		
		this.tabPanel = Ext.create('Ext.tab.Panel',{
			activeTab:this.activeTab,
			layout:'fit',
			defaults:{
				layout:'fit',
				scrollable: true
			},
			items:[this.fieldsEditor,this.associationsGrid,this.validationsGrid]
		});
		
		this.buttons = [{
			text:desLang.save,
    	    handler:this.saveData,
    	   	scope:this
       	},{
			text:desLang.close,
			scope:this,
			handler:this.close
       	}];
		
		this.items = [this.tabPanel];
		this.callParent(arguments);
	},
	importFromOrm:function(){
		var win = Ext.create('designer.ormSelectorWindow',{});
		win.on('select',function(objectName, fields){
			this.setLoading(true);
			Ext.Ajax.request({
				url:app.createUrl([designer.controllerUrl ,'model',''])+'importormfields/',
				method: 'post',
				scope:this,
				params:{
					object:this.objectName,
					objectName:objectName,
					'fields[]':fields
				},
				success: function(response, request) {
					response =  Ext.JSON.decode(response.responseText);
					if(response.success){
						this.fieldsStore.load();
					}else{
						Ext.Msg.alert(appLang.MESSAGE, response.msg);
					}
					this.setLoading(false);
				},
				failure:function() {
					this.setLoading(false);
					Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
				}
			});
		} , this);
		
		win.show();
	},
	importFromDb:function(){
		var win = Ext.create('designer.importDBWindow',{
			title:desLang.importDb
		});
		win.on('select',function(fields, connectionId, table , contype){
			this.setLoading(true);
			Ext.Ajax.request({
				url:app.createUrl([designer.controllerUrl ,'model',''])+'importdbfields/',
				method: 'post',
				scope:this,
				params:{
					object:this.objectName,
					'fields[]':fields,
					connectionId:connectionId,
					table:table,
					type:contype
				},
				success: function(response, request) {
					response =  Ext.JSON.decode(response.responseText);
					if(response.success){
						this.fieldsStore.load();
					}else{
						Ext.Msg.alert(appLang.MESSAGE, response.msg);
					}
					this.setLoading(false);
				},
				failure:function() {
					this.setLoading(false);
					Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
				}
			});
		},this);
		win.show();
	},
	fieldsHasDirtyRecords:function(){
    	var has = false;
    	this.fieldsStore.each(function(record){
    		if(record.dirty || record.phantom){
    			has = true;
    		}
    	},this);  	
		return has;
	},
	addField:function(){
		var me = this;
		
	    Ext.MessageBox.prompt(appLang.MESSAGE , desLang.enterFieldName,function(btn , text){
			 if(btn !='ok'){
				 return;
			 }
			 me.setLoading(true);
			 Ext.Ajax.request({
					url:app.createUrl([designer.controllerUrl ,'model','addfield']),
					method: 'post',
					scope:me,
					params:{
						object:me.objectName,
						id:text
					},
					success: function(response, request) {
						response =  Ext.JSON.decode(response.responseText);
						if(response.success){
							var rec;
							var item  = response.data;
							rec = Ext.create('designer.model.fieldsModel',{name:item.name,type:item.type});
							me.fieldsStore.add(rec);
						}else{
							Ext.Msg.alert(appLang.MESSAGE, response.msg);
						}
						me.setLoading(false);
					},
					failure:function() {
						me.setLoading(false);
						Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
					}
				}); 
	    });
	},
	removeField:function(record){
		var me = this;
		
		Ext.Ajax.request({
			url:app.createUrl([designer.controllerUrl ,'model','removefield']),
			method: 'post',
			scope:me,
			params:{
				object:me.objectName,
				id:record.get('name')
			},
			success: function(response, request) {
				response =  Ext.JSON.decode(response.responseText);
				if(response.success){
					me.fieldsStore.remove(record);
				}else{
					Ext.Msg.alert(appLang.MESSAGE, response.msg);
				}
				me.setLoading(false);
			},
			failure:function() {
				me.setLoading(false);
				Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
			}
		}); 
	},
	addAssociation:function(){
		var store = this.associationsGrid.getStore();
		var count = store.getCount();
		var r = Ext.create('designer.model.associationsModel', {});
		store.insert(count, r);

		this.associationsGrid.getPlugin().startEditByPosition({row: count, column: 0});
	},
	addValidation:function(){
		var store = this.validationsGrid.getStore();
		var count = store.getCount();
		var r = Ext.create('designer.model.validationsModel', {});
		store.insert(count, r);

		this.validationsGrid.getPlugin().startEditByPosition({row: count, column: 0});
	},
	saveData:function(){
		//this.fieldsStore.commitChanges();
		this.associationsGrid.getStore().commitChanges();
		this.validationsGrid.getStore().commitChanges();
		
		this.fireEvent('dataChanged', 
			'',
			Ext.encode(app.collectStoreData(this.associationsGrid.getStore())),
			Ext.encode(app.collectStoreData(this.validationsGrid.getStore()))
		);
	}
});