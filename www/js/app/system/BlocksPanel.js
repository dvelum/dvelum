Ext.define('app.blocksModel', {
	extend: 'Ext.data.Model',
	fields: [
		{name:'id' , type:'integer'},
		{name:'title' , type:'string'},
		{name:'deleted' , type:'boolean'},
		{name:'is_system' , type:'string'},
		{name:'published', type:'boolean'}
	]
});

/**
 * Blocks mapping panel
 * @author Kirill Egorov 2011
 * @var {Ext.Panel}
 *
 * @event configLoaded
 */
Ext.define('app.blocksPanel',{
	extend:'Ext.Panel',
	/**
	 * Data Item Id
	 * @property integer
	 */
	dataId:null,
	/**
	 * @property {Ext.dataJsonStorel}
	 */
	itemsStore:null,
	placesStores:null,
	placesContainer:null,
	placeLinks:null,
	canEdit:false,
	theme:'default',
	border:false,
	/**
	 * @property {Ext.list.ListView}
	 */
	itemsList:null,

	controllerUrl:null,

	aviableBlocks:null,
	curData:null,

	constructor: function(config) {
		config = Ext.apply({
			layout:'border',
			scrollable:true
		}, config || {});
		this.placeLinks = [];
		this.callParent(arguments);
	},

	setData:function(data){

		if(Ext.isEmpty(data)){
			return;
		}

		this.itemsStore.loadData(Ext.clone(this.aviableBlocks));
		this.prepareContainers(data.config);
		this.placeBlocks(data.blocks);
	},

	placeBlocks:function(data){
		this.curData = data;
		Ext.each(this.placeLinks,function(obj){
			if(Ext.isEmpty(data) || Ext.isEmpty(data[obj.code]) || !Ext.isArray(data[obj.code])){
				return;
			}
			Ext.each(data[obj.code],function(blockcfg){
				var rec =  new app.blocksModel(blockcfg);
				/**
				 * Skip if block container was not found
				 */
				if(Ext.isEmpty(obj.link.getStore())){
					return;
				}
				obj.link.getStore().insert(obj.link.getStore().getCount(),rec);

				var index = this.itemsStore.findExact('id',rec.get('id'));
				if(index!=-1)
					this.itemsStore.removeAt(index);
			},this);

		},this);

	},
	/**
	 * Collect component data into the object
	 */
	collectData:function(){
		var params = {};
		Ext.each(this.placeLinks, function(object){
			var ids = [];
			params[object.code] = [];
			object.link.getStore().each(function(record){
				params[object.code].push({'id':record.get('id'),'title':record.get('title')});
			},this);
		},this);
		return {'blocks':Ext.JSON.encode(params)};
	},
	initComponent:function(){

		this.itemsStore = Ext.create('Ext.data.Store',{
			model:'app.blocksModel',
			autoLoad:false,
			proxy: {
				type: 'ajax',
				url:this.controllerUrl + 'blocklist',
				reader: {
					type: 'json',
					rootProperty: 'data',
					idProperty: 'id'
				},
				simpleSortMode: true
			},
			sorters: [{
				property : 'title',
				direction: 'DESC'
			}]
		});


		this.itemsList = Ext.create('Ext.grid.Panel',{
			region:'east',
			store: this.itemsStore,
			multiSelect: false,
			columnResize:false,
			columnSort:false,
			width:250,
			title:appLang.AVAILABLE_BLOCKS,
			hideHeaders:true,
			frame:false,
			columns: [{
				text: appLang.AVAILABLE_BLOCKS,
				dataIndex: 'title',
				flex:1,
				renderer:this.columnRenderer
			}],
			multiSelect: true,
			viewConfig: {
				plugins: {
					ptype: 'gridviewdragdrop',
					dragGroup:'itemDDGroup',
					dropGroup:'itemDDGroup',
					enableDrag:this.canEdit,
					enableDrop:this.canEdit
				}
			}
		});


		this.placesContainer = Ext.create('Ext.panel.Panel',{
			region:'center',
			layout:'fit',
			frame:false,
			split:true,
			title:appLang.PLACES,
			scrollable:true,
			collapsible:false,
			items:[{
				xtype:'label',
				text: appLang.MSG_CHOOSE_PAGE_THEME,
				margin: '10 10 10 10'
			}]
		});

		this.items = [this.placesContainer, this.itemsList];
		this.callParent(arguments);
		this.loadAviableBlocks();
		this.on('beforedestroy',this.clearContainers,this);
	},
	loadAviableBlocks:function(){
		var handle = this;
		Ext.Ajax.request({
			url:this.controllerUrl + 'blocklist',
			method: 'post',
			success: function(response, request) {
				response =  Ext.JSON.decode(response.responseText);
				if(response.success){
					handle.aviableBlocks = response.data;
					handle.itemsStore.loadData(Ext.clone(handle.aviableBlocks));
				}else{
					Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_CANT_LOAD_BLOCKS);
				}
			},
			failure:function() {
				Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
			}
		});
	},
	clearContainers:function(){
		this.placesContainer.removeAll();
	},
	prepareContainers:function(data){

		var itemsList = [];

		this.placeLinks = [];

		Ext.Array.each(data.items,function(item,index){

			if(Ext.isEmpty(item.isElContainer) || !item.isElContainer)
			{
				var itemCfg = Ext.apply({title:item.code,bodyCls :'nonContainerBlock'},item);
				var itemObj = Ext.create('Ext.panel.Panel',itemCfg);
			}else{

				var itemStore = Ext.create('Ext.data.Store',{
					model:'app.blocksModel',
					proxy:{type:'ajax'},
					reader:{type:'json'}
				});

				var itemCfg = Ext.apply({
					title:item.code,
					store:Ext.create('Ext.data.Store',{
						model:'app.blocksModel',
						proxy:{type:'ajax'},
						reader:{type:'json'}
					}),
					hideHeaders:true,
					minHeight:20,
					//constrain:true,
					multiSelect: true,
					scrollable:true,
					viewConfig: {
						plugins: {
							ptype: 'gridviewdragdrop',
							dropGroup:'itemDDGroup',
							dragGroup:'itemDDGroup',
							enableDrag:this.canEdit,
							enableDrop:this.canEdit
						},
						copy:false
					},
					columns:[{
						dataIndex: 'title',
						flex:1,
						renderer:this.columnRenderer
					},{
						xtype:'actioncolumn',
						width:30,
						items:[
							{
								width:30,
								iconCls:'deleteIcon',
								scope:this,
								tooltip: appLang.REMOVE_ITEM,
								handler: function(grid, rowIndex, colIndex) {
									var rec = grid.getStore().getAt(rowIndex);
									this.itemsList.getStore().insert(this.itemsList.getStore().getCount(),rec);
									grid.getStore().remove(rec);
								}
							}
						]
					}]
				},item);

				var itemObj =  Ext.create('Ext.grid.Panel',itemCfg);
				this.placeLinks.push({code:item.code,link:itemObj});

			}
			itemsList.push(itemObj);
		},this);


		var cfg = {
			xtype:'panel',
			layout: {
				type: 'table',
				columns: data.columns
			},
			border:true,
			defaults:{
				border:true
			},
			autoHeight:true,
			scrollable:true,
			items:itemsList
		};


		this.blocksContainer = Ext.create('Ext.panel.Panel',cfg);
		this.clearContainers();

		this.placesContainer.add(this.blocksContainer);

	},
	columnRenderer :function(value, metaData, record, rowIndex, colIndex, store)
	{
		metaData.attr = 'style="display:block;background-color:#EFEFEF;white-space:normal;border: 1px solid #333333; height:50px;cursor:pointer;"';

		if(record.get('deleted')){
			return  '<img src="'+app.wwwRoot+'i/system/trash.png" data-qtip="Instance was deleted" align="left">' + value;
		}

		if(record.get('published')){
			value = '<img src="'+app.wwwRoot+'i/system/yes.gif" data-qtip="Published" align="left">' + value;
		}else{
			value = '<img src="'+app.wwwRoot+'i/system/no.png" data-qtip="Not published" align="left">' + value;
		}

		return value;
	},
	loadConfig: function(name){
		var handle = this;

		Ext.Ajax.request({
			url:this.controllerUrl + 'blockconfig',
			method: 'post',
			params:{
				theme:name
			},
			success: function(response, request) {
				response =  Ext.JSON.decode(response.responseText);
				if(response.success){
					var data = {
						config:	response.data,
						blocks: handle.curData
					};
					handle.setData(data);
					handle.fireEvent('configLoaded');
				}else{
					Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_CANT_LOAD_BLOCKS_CONFIG);
				}
			},
			failure:function() {
				Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
			}
		});
	}
});