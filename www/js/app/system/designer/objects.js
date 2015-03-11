
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
Ext.define('designer.objects.Panel',{
	extend:'Ext.tab.Panel',
	activeTab:0,
	title:desLang.layoutObjects,

	panelsTab:null,
	storesTab:null,
	modelsTab:null,

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

		this.panelsTab = Ext.create('designer.objects.Tree',{
			title:desLang.panels,
			listType:'visual',
			controllerUrl:this.controllerUrl,
			listeners:curListeners
		});

		this.storesTab = Ext.create('designer.objects.Grid',{
			title:desLang.stores,
			controllerUrl:this.controllerUrl,
			dataStore:this.createStore('stores'),
			listeners:curListeners
		});

		this.modelsTab = Ext.create('designer.objects.Grid',{
			title:desLang.models,
			controllerUrl:this.controllerUrl,
			dataStore:this.createStore('models'),
			listeners:curListeners
		});


		this.items = [this.panelsTab , this.storesTab , this.modelsTab];

		this.callParent();
	},
	/**
	 * Clear project data
	 */
	clearData:function(){
		this.panelsTab.getStore().getRootNode().removeAll();
		this.storesTab.dataStore.removeAll();
		this.modelsTab.dataStore.removeAll();
		this.menuStore.removeAll();
	},
	/**
	 * Load objects info
	 */
	loadInfo:function(){
		this.panelsTab.reload();
		this.storesTab.dataStore.load();
		this.modelsTab.dataStore.load();
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