Ext.define('designer.methodsModel',{
	extend:'Ext.data.Model',
	fields:[
		{name:'object', type:'string'},
		{name:'method', type:'string'},
		{name:'params', type:'string'},
		{name:'has_code', type:'boolean'}
	],
	idProperty:'method'
});

/**
 *
 * @event methodsUpdated
 */
Ext.define('designer.methodsPanel',{
	extend:'Ext.grid.Panel',
	objectName:'',
	controllerUrl:'',
	columnLines:true,
	addButton:null,
	autoLoadData:true,
	extraParams:null,
	searchField:false,
	
	constructor:function(config){
		Ext.apply({extraParams:{}} , config || {});
		this.callParent(arguments);
	},
	initComponent:function()
	{	
		if(!this.controllerUrl.length){
			this.controllerUrl = app.createUrl([designer.controllerUrl ,'methods','']);
		}
		
		this.extraParams['object'] = this.objectName;
				
		this.store = Ext.create('Ext.data.Store',{
			model:'designer.methodsModel',
			proxy: {
		        type: 'ajax',
		    	url:this.controllerUrl +  'objectmethods',
		        reader: {
		            type: 'json',
					rootProperty: 'data'
		        },
		        extraParams:this.extraParams,
			    simpleSortMode: true
		    },
	        remoteSort: false,
		    autoLoad: this.autoLoadData,
		    sorters: [{
                property : 'object',
                direction: 'DESC'
            },{
                property : 'method',
                direction: 'DESC'
            }]
		});
		
		this.searchField = Ext.create('SearchPanel',{
			store:this.store,
			local:true,
			width:130,
			hideLabel:true,
			fieldNames:['method']
		});
		
		this.addButton = Ext.create('Ext.Button',{
			iconCls:'addIcon',
			text:desLang.addMethod,
			scope:this,
			handler:this.addMethod
		});
		
		this.tbar = [this.addButton,'->',this.searchField];
		
		this.columns = [
		      {
		    	  xtype:'actioncolumn',
		    	  width:20,
		    	  items:[
		    	         {
		    	        	 width:20,
		    	        	 tooltip:desLang.edit,
		    	        	 scope:this,
		    	        	 iconCls:'editIcon',
		    	        	 handler:function(grid, rowIndex){
				        		 var rec = grid.getStore().getAt(rowIndex);
				        		 this.editMethod(rec);
				        	 }
		    	         }
		    	  ]
		      },{
		    	 dataIndex:'method',
		    	 text:desLang.method,
		    	 flex:1
		     },{
		    	 dataIndex:'params',
		    	 text:desLang.params,
		    	 flex:1
		     },{
				  xtype:'actioncolumn',
				  width:25,
				  items:[
				         {
				        	 iconCls:'deleteIcon',
				        	 tooltip:desLang.removeAction,
				        	 handler:function(grid, rowIndex){
				        		 var rec = grid.getStore().getAt(rowIndex);
				        		 this.removeMethod(rec);
				        	 },
				        	 width:25,
				        	 scope:this
				         }
				  ]
			  }  	
		];
		
		this.on('celldblclick', function(table,  td,  cellIndex,  record){
			this.editMethod(record);
		},this);
		
		this.callParent();
	},
	/**
	 * Show "Create method" dialog
	 */
	addMethod:function(){
		 Ext.MessageBox.prompt(appLang.MESSAGE , desLang.enterMethodName,function(btn , methodName){
			 if(btn !=='ok'){
				 return;
			 }
			 var params = Ext.clone(this.extraParams);			
			 params['method'] = methodName;
			 
			 var store = this.getStore();
			 			 
			 Ext.Ajax.request({
				 	url:this.controllerUrl + 'addmethod',
				 	method: 'post',
				 	scope:this,
				 	params:params,
				    success: function(response) {
				 		response =  Ext.JSON.decode(response.responseText);
				 		if(!response.success){	 			
				 			Ext.Msg.alert(appLang.MESSAGE,response.msg);
				 			return;
				 		}		 		
				 		store.load({
				 			scope:this,
				 			callback:function(){
				 				var index = store.findExact('method' , methodName);
				 				if(index !==-1){
				 					this.editMethod(store.getAt(index));
				 				}
				 			}
				 		});
						this.fireEvent('methodsUpdated');
				    },
				    failure:function() {
				       	Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);   
				    }
		    });
		 },this);
	},
   /**
	* Remove method
	* @param {Ext.data.Model} record
	*/ 
	removeMethod:function(record){		
		var params = Ext.clone(this.extraParams);			
		params['method'] = record.get('method');
		Ext.Ajax.request({
		 	url:this.controllerUrl +'removemethod',
		 	method: 'post',
		 	scope:this,
		 	params:params,
		    success: function(response) {
		 		response =  Ext.JSON.decode(response.responseText);
		 		if(!response.success){	 			
		 			Ext.Msg.alert(appLang.MESSAGE,response.msg);
		 			return;
		 		}		 		
		 		designer.msg(appLang.MESSAGE , desLang.msg_methodRemoved);
		 		this.getStore().remove(record);
				this.fireEvent('methodsUpdated');
		    },
		    failure:function() {
		       	Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);   
		    }
		 });
	},
   /**
	* Edit method
	* @param {Ext.data.Model} record
	*/ 
	editMethod:function(record)
	{
		Ext.create('designer.methodEditorWindow',{
			controllerUrl:this.controllerUrl,
			objectName:this.objectName,
			methodName:record.get('method'),
			paramsString:record.get('params'),
			extraParams:this.extraParams,
			modal:true,
			listeners:{
				'codeSaved':{
					fn:function(){
						this.getStore().load();
						this.fireEvent('methodsUpdated');
					},
					scope:this
				}
			}
		}).show();
	},
	/**
	 * Get search filter text
	 * @return string
	 */
	getSearchText:function(){
	    if(this.searchField){
            return this.searchField.getValue();
        }else{
	        return '';
        }
	},
	/**
	 * Set search filter
	 * @param {string} text
	 */
	setSearchText:function(text){
		this.searchField.setValue(text);
	},
    destroy:function(){
		this.searchField.destroy();
        this.store.destroy();
        this.addButton.destroy();
        this.callParent(arguments);
    }
});

