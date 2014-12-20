Ext.define('designer.relatedProjectItemsWindow',{
	extend:'Ext.Window',
	title:desLang.relatedProjectItems,
	width:600,
	height:500,
	objectsTree:null,
	dataStore:null,
	listUrl:'',
	layout:'fit',
	
	initComponent:function(){		
		this.dataStore = Ext.create('Ext.data.TreeStore',{
			proxy: {
			        type: 'ajax',
			    	url:this.listUrl,
			    	reader: {
			            type: 'json',
			            idProperty: 'id'
			        },
			        autoLoad:false
			},
			fields: [
			         {name:'id' ,  type:'string'},
			         {name:'text' , type:'string'},
			         {name:'objClass',type:'string'}
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
		
		this.objectsTree = Ext.create('Ext.tree.Panel',{
			store:this.dataStore,
		    rootVisible:false,
		    useArrows: true
		});
				
		this.items = [this.objectsTree];		
		this.callParent();
		
		this.dataStore.getRootNode().removeAll();
		this.dataStore.load();
	}
});