
Ext.ns('designer.object');
Ext.ns('designer.objects');

Ext.define('designer.objects.Model',{
	extend:'Ext.data.Model',
	fields: [
		{name:'id' ,  type:'string'},
		{name:'title' , type:'string'},
		{name:'objClass',type:'string'}
	]
});

Ext.define('designer.objects.Store',{
	extend:'Ext.data.Store',
	model:'designer.objects.Model',
	remoteSort: false,
	autoLoad: false,
	sorters: [{
		property : 'title',
		direction: 'DESC'
	}],
	constructor:function(config){
		config = Ext.apply({
			proxy: {
				type: 'ajax',
				url: (config.controllerUrl || '') + 'list',
				reader: {
					type: 'json',
					rootProperty: 'data',
					idProperty: 'id'
				},
				extraParams:config.extraParams || {},
				simpleSortMode: true
			}
		},  config || {});
		this.callParent(arguments);
	}
});

/**
 * Project structure panel
 *
 * @event dataSaved
 * @param id
 * @param objectClass
 * @param title
 *
 * @event itemSelected
 *
 * @event dataChanged
 *
 * @event dataChanged
 *
 * @event objectRemoved
 */
Ext.define('designer.objects.Manager',{
	extend:'Ext.container.Container',
	layout:'fit',
	componentsTree:null,
	storesStore:null,
	modelsStore:null,
	menuStore:null,
	controllerUrl:'',

	initComponent:function(){

		this.menuStore =  this.createStore('menu');
		var me = this;
		var curListeners = {
			itemSelected:{
				fn:function(id , objectClass , title , isInstance){
					me.fireEvent('itemSelected' , id , objectClass , title , isInstance);
				},
				scope:me
			},
			dataChanged:{
				fn:function(){
					me.fireEvent('dataChanged');
				},
				scope:me
			},
			objectRemoved:{
				fn:function(){
					me.fireEvent('objectRemoved');
				},
				scope:me
			}
		};

		this.componentsTree = Ext.create('designer.objects.Tree',{
			listType:'visual',
			controllerUrl:this.controllerUrl,
			listeners:curListeners
		});

		this.storesStore = this.createStore('stores');
		this.modelsStore = this.createStore('models');

		this.items = [this.componentsTree];

		this.callParent();
	},
	/**
	 * Clear project data
	 */
	clearData:function(){
		this.componentsTree.getStore().getRootNode().removeAll();
		this.storesStore.removeAll();
		this.modelsStore.removeAll();
		this.menuStore.removeAll();
	},
	/**
	 * Load objects info
	 */
	loadInfo:function(){
		this.componentsTree.reload();
		this.storesStore.load();
		this.modelsStore.load();
		this.menuStore.load();
	},
	/**
	 * Create data Store
	 * @param type - listType extraParam
	 * @returns {Ext.data.Store}
	 */
	createStore:function(type){
		var s = Ext.create('designer.objects.Store',{
			controllerUrl: this.controllerUrl,
			extraParams:{
				type:type
			}
		});
		return s;
	}
});