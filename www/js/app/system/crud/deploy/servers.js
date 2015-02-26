Ext.define('app.crud.orm.deploy.Servers',{
	extend:'Ext.grid.Panel',
	controllerUrl:'',
	columnLines:true,
	canEdit:false,
	canDelete:false,
		
	initComponent:function(){

		this.store = Ext.create("Ext.data.Store",{
			autoLoad:true,
			model:'app.comboStringModel',
			proxy:{
				type:"ajax",
				simpleSortMode:true,
				url:this.controllerUrl + 'list',
				reader:{
					idProperty:"id",
					rootProperty:"data"
				}	
			}	
		});
		
		this.columns = [{
		    dataIndex:'title',
		    text:appLang.TITLE,
		    flex:1
		}];

		if(this.canDelete)
		{
			this.columns.push(
				{
					xtype:'actioncolumn',
	            	align:'center',
	            	width:30,
	            	items:[{
        	    		tooltip:appLang.DELETE_RECORD,
        	    	   	iconCls:'deleteIcon',
        	    	   	width:30,
        	    	   	scope:this,
        	    	   	handler:this.removeServer
					}]
				}
			);
		}

		if(this.canEdit){
			this.tbar = [
			     {
			    	 text:appLang.ADD_SERVER,
			    	 iconCls:'plusIcon',
			    	 scope:this,
			    	 handler:function(){this.showEditServerWindow(false);}
			     }
			];
		}
		
		this.listeners={
	    	'itemdblclick':{
	    		fn:function(view , record , number , event , options){
	    			 this.showEditServerWindow(record.get('id'));
	    		},
	    		scope:this
	    	}
	    };
		
		
		this.callParent();
		
		this.addEvents(
	            /**
	             * @event dataSaved
	             * @param string serverId
	             * @param string serverName
	             */
	           'serverSelected'
	    );  
		
		this.getSelectionModel().on('selectionchange',function(){
			var sm = this.getSelectionModel();
			if(sm.hasSelection()){
				var record = sm.getSelection()[0];
				this.fireEvent('serverSelected' , record.get('id'), record.get('title'));
			}
		},this);
	},
	/**
	 * Remove server config
	 */
	removeServer:function(grid, rowIndex, colIndex){
		var store = grid.getStore();
		var record = store.getAt(rowIndex);
		
		var me = this;
		
   		Ext.Msg.confirm(appLang.CONFIRM, appLang.MSG_CONFIRM_DELETE_SERVER + ' ' + record.get('title') , function(btn){
   			if(btn != 'yes'){
   				return false;
   			}
   			
			Ext.Ajax.request({
				url: me.controllerUrl + 'remove',
			   	method: 'post',
			   	scope:me,
			   	params:{
			 		'id':record.get('id')
			 	},
			   	success: function(response, request) {
					response =  Ext.JSON.decode(response.responseText);
				   	if(response.success){
				   		store.removeAt(rowIndex);
				   	} else {
					   	Ext.Msg.alert(appLang.MESSAGE , response.msg);
				   	}
			   	},
			   	failure:function() {
				   	Ext.Msg.alert(appLang.MESSAGE , appLang.MSG_LOST_CONNECTION);
			   	}
		   });
   		}, this);
	},
	/**
	 * Show server config window
	 */
	showEditServerWindow:function (serverId){
		var title = appLang.EDIT_SERVER_CONFIG;
		if(serverId === false){
			title = appLang.NEW_SERVER_CONFIG;
		}		
		var win  = Ext.create('app.crud.orm.deploy.ServersEditWindow',{
			canEdit:this.canEdit,
			controllerUrl:this.controllerUrl,
			dataItemId:serverId,
			title:title
		});		
		win.on('dataSaved',function(){
			this.getStore().load();
		},this);	
		win.show();
	}
});


Ext.define('app.crud.orm.deploy.ServersEditWindow',{
	extend:'Ext.Window',
	modal:true,
	controllerUrl:'',
	canEdit:false,
	width:310,
	height:300,
	dataForm:null,
	layout:'fit',
	
	initComponent:function(){
		
		this.dataForm = Ext.create('Ext.form.Panel', {
		    items:[
				Ext.create('Ext.tab.Panel',{
				    plain:true,
					deferredRender:false,
					activeItem: 0, 
					enableTabScroll:true,
					border:false,
					frame:false,
					style:{
						backgroundColor:'#E5E4E2'
					},
					defaults:{
						border:false,
						frame:true,
				       	border:false,
				       	layout:'anchor',
				       	bodyPadding:'3px',
				        defaults:{
				            labelWidth: 115			        	
				        }
					},
					items:[
					       {
					    	   xtype:'panel',
					    	   title:appLang.GENERAL,
					    	   border:false,
					    	   frame:false,
					    	   layout:'form',
					    	   fieldDefaults: {
						            labelAlign: 'right',
						            anchor: '100%',
						            allowBlank:false
						       },
					    	   items:[
					    	          {
					    	        	xtype:'textfield',
					    	        	fieldLabel:appLang.SERVER_ID,
					    	        	vtype:'alphanum',
					    	        	name:'id'
					    	          },
					    	          {
					    	        	  xtype:'textfield',
					    	        	  fieldLabel:appLang.SERVER_NAME,
					    	        	  name:'name'
					    	          },{
					    	        	  xtype:'textfield',
					    	        	  fieldLabel:appLang.SERVER_API_URL,
					    	        	  name:'url'
					    	          },{
					    	        	  xtype:'textfield',
					    	        	  fieldLabel:appLang.SERVER_API_KEY,
					    	        	  inputType:'password',
					    	        	  name:'key'
					    	          }
					    	   ]
					       }
				    ]
				})
				
		    ]
		});			
		
		
		if(this.canEdit){
			this.buttons = [
			     {
			    	 text:appLang.SAVE,
			    	 scope:this,
			    	 handler:this.saveData
			     },{
			    	 text:appLang.CLOSE,
			    	 scope:this,
			    	 handler:this.close
			     }
			];
		}
		
		
		this.items = [this.dataForm];
		
		this.callParent();
		
		this.addEvents(
	            /**
	             * @event dataSaved
	             */
	           'dataSaved'
	       );  
		
		if(this.dataItemId!==false){
	    	 this.loadData(this.dataItemId);
	    }
	},
  /**
   * Load server config
   * @param integer itemId - record id
   */
	loadData: function(itemId)
	{		
		  var handle = this;
		  this.dataForm.getForm().findField('id').setReadOnly(true);
		  this.dataForm.getForm().load({
			  waitMsg:appLang.LOADING,
			  url:this.controllerUrl + 'config',
				method:'post',
			    params: {
			        'id':itemId
			    },
			    success: function(form, action) 
				{	
	   		 		if(action.result.success)
	   		 		{   			 		   	
	   		 		  handle.dataForm.getForm().findField('id').hide();
	   		 		}
	   		 		else
	   		 		{
	   		 			Ext.Msg.alert(appLang.MESSAGE, action.result.msg);
	   		 			handle.close();
	   		 		}	
	   	        },
	   	        failure: app.formFailure
		  });
	  },
	  /**
	   * Submit server config
	   */
	  saveData: function()
	  {		  
		    var handle = this;
		    var form = this.dataForm.getForm();
		    
		 	form.submit({
				clientValidation: true,
				waitTitle:appLang.SAVING,
				method:'post',
				url:this.controllerUrl + 'save',
				success: function(form, action) 
				{	
	   		 		if(!action.result.success){
	   		 			Ext.Msg.alert(appLang.MESSAGE, action.result.msg);
	   		 			return;
	   		 		} 
	 				handle.fireEvent('dataSaved');	 			
	 			    handle.close();
	   	        },
	   	        failure: app.formFailure
			});
	  }
});