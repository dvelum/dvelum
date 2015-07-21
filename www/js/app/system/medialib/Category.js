Ext.ns('app.medialib');
/**
 *
 *
 * @event itemSelected id
 *
 * @event itemsPlaced
 *
 */
Ext.define('app.medialib.CategoryTree',{
	extend:'Ext.tree.Panel',
	controllerUrl:'',
	
	canEdit:false,
	canDelete:false,
	
	constructor:function(config){
		config = Ext.apply({
	        rootVisible:true,
	        useArrows: false
	    }, config || {});				
        this.callParent(arguments);
	},
	
	initComponent:function(){
		var tbar = [];
		
		if(this.canEdit){
			
			this.viewConfig={
				plugins: {
		            ptype: 'treeviewdragdrop',
		            ddGroup: 'medialibraryItem',
		            displayField: 'title'
		        },
				listeners:{
					drop:{
						fn:this.sortChanged,
						scope:this
					}
				}
			}
		
			tbar.push({
		    	  iconCls:'plusIcon',
		    	  text:appLang.ADD_ITEM,
		    	  handler:function(){
		    		  var sm = this.getSelectionModel();
		    		  if(sm.hasSelection()){
		    			  this.showCategoryEditor(0 , sm.getSelection()[0].get('id'));
		    		  }else{
		    			  this.showCategoryEditor(0);
		    		  }
		    	  },
		      	  scope:this
		    });
		}
		
		if(this.canDelete){
			tbar.push('->');
			tbar.push({
		    	  iconCls:'deleteIcon',
		    	  text:appLang.DELETE_ITEM,
		    	  handler:function(){
		    		 var sm = this.getSelectionModel();
		    		 if(!sm.hasSelection()){
		    			 return;
		    		 }
		    		 this.deleteRecord(sm.getSelection()[0]);
		    	  },
		    	  scope:this
		    });
		}
		
		if(tbar.length){
			this.tbar = tbar;
		}
		
		this.store = Ext.create('Ext.data.TreeStore',{
			proxy: {
			        type: 'ajax',
			    	url:this.controllerUrl + 'treelist',
			    	reader: {
			            type: 'json',
			            idProperty: 'id'
			        }
			},
			root: {
			        text:'/',
			        expanded: true,
			        dragable:false,
			        id:0
			},
			listeners:{
				load:{
					fn:function(){						
						this.getSelectionModel().select(0);
					},
					scope:this
				}
			}
		});
		
		this.callParent(arguments);
		
		this.on('itemdblclick' , function(view, record, element , index , e , eOpts){
			if(record.get('id') !== 0){
				this.showCategoryEditor(record.get('id'));
			}
		},this);
		
		this.getSelectionModel().on('selectionchange',function(sm, selected, options){			
			if(!sm.hasSelection()){
				this.fireEvent('itemSelected' , 0);
				return;
			}
			var rec = selected[0];
			this.fireEvent('itemSelected' , rec.get('id'));
		},this);
		
		if(this.canEdit){
			var view = this.getView();
			view.on('beforedrop', function(node, data, overModel, dropPosition, dropHandlers) {
				if(data.records[0].get('path')){
					dropHandlers.cancelDrop();						
					var parentNode = null;
					if(dropPosition == 'append'){
						parentNode = overModel;
					}else{
						parentNode = overModel.parentNode;
					}
					this.addMediaItems(data.records , parentNode.get('id'));
				}		    
			},this);
		}
	},
	sortChanged:function( node, data,  overModel,  dropPosition, options){

		if(!this.canEdit){
			return;
		}
		var parentNode = null;
		if(dropPosition == 'append'){
			parentNode = overModel;
		}else{
			parentNode = overModel.parentNode;
		}
		var view = this.getView();		
		var childsOrder = []; 
		
		parentNode.eachChild(function(node){
			childsOrder.push(node.getId());
		},this);

		 Ext.Ajax.request({
		    url: this.controllerUrl + 'sortcatalog',
			method: 'post',
			params:{
		 			'id':data.records[0].get('id'),
		 			'newparent':parentNode.get('id'),
		 			'order[]' : childsOrder
		 	},
	        success: function(response, request) {
				response =  Ext.JSON.decode(response.responseText);
				if(response.success){				
					 return;
				}else{
					Ext.Msg.alert(appLang.MESSAGE, response.msg);
				}	
	      },
	      failure: app.formFailure
		});
	 },
	 showCategoryEditor:function(id , parent_id)
	 {
		 if(parent_id == undefined){
			 parent_id = 0;
		 }
		 var win = Ext.create('app.editWindow',{
			 title:appLang.EDIT,
			 controllerUrl:this.controllerUrl,
			 canEdit:this.canEdit,
			 canDelete:false,
			 useTabs:false,
			 showToolbar:false,
			 hideEastPanel:true,
			 objectName:'mediacategory',
			 dataItemId:id,
			 width:300,
			 height:150,
			 items:[{
		        		xtype:'textfield',
		        		fieldLabel:appLang.TITLE,
		        		name:'title',
		        		labelWidth:70
				   },{
					    xtype:'hidden',
						name:'parent_id',
						value:parent_id
				   }
			 ]
		 });
		 
		 win.on('dataSaved', function(){			 
			 var itemId = win.dataItemId;
			 var itemText = win.getForm().getForm().findField('title').getValue();
			 var rootNode = this.getRootNode();
			 var node;
			 		 
			 if(!id)
			 {
				 if(!parent_id)
				 {
					 node = rootNode.appendChild({
					        id: itemId,
					        text: itemText,
					        leaf: false,
					        dragable:true,
					        children:[]
					 });
				 }else{
					 node = rootNode.findChild("id", parent_id , true).appendChild({
					        id: itemId,
					        text: itemText,
					        leaf: false,
					        dragable:true,
					        children:[]
					 });
				 }
			 }else{
				 node = rootNode.findChild("id", id , true)
			 }
			
			 node.set('text' , itemText);
			 node.commit();
			 win.close();
		 } , this);
		 
		 win.show();
	 },
	 reloadData:function(){		 
		this.store.getRootNode().removeAll();
		this.store.load();
	 },
	 /**
	  * Delete page record
	  * @param {Ext.data.Record}
	  */
	 deleteRecord:function(record){
		 var me = this;
		 Ext.Msg.confirm(appLang.CONFIRM, appLang.MSG_CONFIRM_DELETE + ' "' + record.get('text')+'"' , function(btn){
	   			if(btn != 'yes'){
	   				return false;
	   			}
	   			Ext.Ajax.request({
				    url: me.controllerUrl + 'delete',
					method: 'post',
					params:{
						'id':record.get('id')
					},				
			        success: function(response, request) {
						response =  Ext.JSON.decode(response.responseText);
						if(response.success){										 
							record.remove();
							me.updateLayout();
						}else{
							Ext.Msg.alert(appLang.MESSAGE, response.msg);
						}	
			      },
			      failure: function(){
			    	  Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
			    	  blockMap.unmask();
			      }
			});
	   	 });
	 },
	 /**
	  * Change Medilibrary items catalog
	  * @param {Array} items
	  * @param integer catalogId
	  */
	 addMediaItems:function(items , catalogId){
		 var mediaItems = [];
		 var me = this;
		 
		 Ext.each(items,function(item){
			 mediaItems.push(item.get('id'));
		 });
		 
		 Ext.Ajax.request({
		    url: me.controllerUrl + 'placeitems',
			method: 'post',
			params:{
				'items':Ext.JSON.encode(mediaItems),
				'catalog':catalogId
			},				
	        success: function(response, request) {
				response =  Ext.JSON.decode(response.responseText);
				if(response.success){										 
					me.fireEvent('itemsPlaced');
				}else{
					Ext.Msg.alert(appLang.MESSAGE, response.msg);
				}	
	      },
	      failure: function(){
	    	  Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
	    	  blockMap.unmask();
	      }
		});
	 }
});