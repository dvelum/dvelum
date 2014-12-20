
Ext.define('designer.objects.Tree',{
    extend:'Ext.Panel',
    controllerUrl:'',
    listType:'visual',	
    layout:'fit',
    firstLoad:true,
    selectedNode: false,
    initComponent:function(){

	this.dataStore = Ext.create('Ext.data.TreeStore',{
	    proxy: {
		type: 'ajax',
		url:this.controllerUrl + 'visuallist',
		reader: {
		    type: 'json',
		    idProperty: 'id'
		},
		autoLoad:false
	    },
	    fields: [
	             {name:'id' ,  type:'string'},
	             {name:'text' , type:'string'},
	             {name:'objClass',type:'string'},
	             {name:'isInstance' , type:'boolean'}
	             ],
	             root: {
	        	 text: '/',
	        	 expanded: true,
	        	 id:0,
	        	 leaf:false,
	        	 children:[]
	             },
	             defaultRootId:0,
	             clearOnLoad:true,
	             autoLoad:false
	});

	this.treePanel = Ext.create('Ext.tree.Panel',{
	    store:this.dataStore,
	    rootVisible:false,
	    useArrows: true,

	    viewConfig:{
		plugins: {
		    ptype: 'treeviewdragdrop'
		},
		listeners:{
		    drop:{
			fn:this.sortChanged,
			scope:this
		    },
		    scope:this
		}

	    }
	});

	this.treePanel.on('select',function(tree, record, index, eOpts){
	    this.selectedNode = record;
	},this);
	
	this.dataStore.on('load',function(){
	    if(this.selectedNode){
		this.treePanel.getSelectionModel().select(this.selectedNode);
		this.treePanel.getSelectionModel().setLastFocused(this.selectedNode);
	    }
	},this);
	
	this.treePanel.addListener('itemclick',function(view, record, element , index , e , eOpts){
	    this.fireEvent('itemSelected' , record.get('id') , record.get('objClass'), record.get('text') , record.get('isInstance'));			
	},this,{buffer:400});

	this.collapseBtn = Ext.create('Ext.Button',{
	    icon:app.wwwRoot + 'i/system/collapse-tree.png',
	    tooltip:desLang.collapseAll,
	    listeners:{
		click:{
		    fn:function(){
			this.treePanel.collapseAll();
			this.collapseBtn.disable();
			this.expandBtn.enable();
		    },
		    scope:this
		}
	    }
	});
	this.expandBtn = Ext.create('Ext.Button',{
	    tooltip:desLang.expandAll,
	    icon:app.wwwRoot + 'i/system/expand-tree.png',
	    disabled:true,
	    listeners:{
		click:{
		    fn:function(){
			this.treePanel.expandAll();
			this.collapseBtn.enable();
			this.expandBtn.disable();
		    },
		    scope:this
		}
	    }
	});

	this.tbar = [this.collapseBtn , this.expandBtn,
	             '->',{
	    tooltip:desLang.remove,
	    iconCls:'deleteIcon',
	    handler:this.removeObject,
	    scope:this
	}           
	];

	this.items = [this.treePanel];
	this.callParent(arguments);
	this.addEvents(
		/**
		 * @event dataSaved
		 * @param id
		 * @param className
		 * @param title
		 */
		'itemSelected',
		/**
		 * @event dataSaved
		 */
		'dataChanged',
		/**
		 * @event objectRemoved
		 */
		'objectRemoved'
	); 

	this.treePanel.on('scrollershow', function(scroller) {
	    if (scroller && scroller.scrollEl) {
		scroller.clearManagedListeners();
		scroller.mon(scroller.scrollEl, 'scroll', scroller.onElScroll, scroller);
	    }
	},this);
    },
    /**
     * Hard code fix for Ext.Tree.Store loading
     * @todo wait for official fix
     */
    reload:function(){
	this.dataStore.getRootNode().removeAll();
	this.dataStore.load();
    },
    getStore:function(){
	return this.dataStore;
    },
    sortChanged:function( node, data, overModel,  dropPosition, options){
	var parentId = 0;
	var parentNode = null;
	if(dropPosition == 'append'){
	    parentId = overModel.get('id');
	    parentNode = overModel;
	}else{
	    parentId = overModel.parentNode.get('id');
	    parentNode = overModel.parentNode;
	}		
	var childsOrder = []; 
	parentNode.eachChild(function(node){
	    childsOrder.push(node.getId());
	},this);

	Ext.Ajax.request({
	    url:this.controllerUrl + 'sort',
	    method: 'post',
	    params:{
		'id':data.records[0].get('id'),
		'newparent':parentId,
		'order[]' : childsOrder
	    },
	    scope:this,
	    success: function(response, request) {
		response =  Ext.JSON.decode(response.responseText);
		if(response.success){				
		    this.fireEvent('dataChanged');
		    this.forceComponentLayout();
		}else{
		    Ext.Msg.alert(appLang.MESSAGE, response.msg);
		}	
	    },
	    failure: app.formFailure
	});
    },
    /**
     * Remove component from project
     */
    removeObject:function(){
	var sm = this.treePanel.getSelectionModel();
	if(!sm.hasSelection() || sm.getSelection()[0].get('id')=='0'){
	    Ext.Msg.alert(appLang.MESSAGE, desLang.msg_selectForRemove);
	    return;
	}
	var me = this;
	var selected = sm.getSelection()[0];

	if(selected.get('objClass')=='Docked'){
	    Ext.Msg.alert(appLang.MESSAGE, desLang.cantDeleteDocked);
	    return;
	}

	Ext.Ajax.request({
	    url:this.controllerUrl + 'remove',
	    method: 'post',
	    params:{
		'id':selected.get('id')
	    },
	    success: function(response, request) {
		response =  Ext.JSON.decode(response.responseText);
		if(response.success){
		    me.fireEvent('objectRemoved');
		    me.fireEvent('dataChanged');
		    me.reload();
		}else{
		    Ext.Msg.alert(appLang.MESSAGE, response.msg);
		}	
	    },
	    failure: app.formFailure
	});
    }

});