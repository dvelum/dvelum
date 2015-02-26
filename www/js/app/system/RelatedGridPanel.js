/**
 * Related Elements Grid
 * @author Kirill Egorov 2011
 * @var {Ext.Panel}
 */

Ext.define('app.relatedGridModel', {
	extend: 'Ext.data.Model',
	fields: [
		{name:'id' , type:'integer'},
		{name:'published' , type:'boolean'},
		{name:'deleted' , type:'boolean'},
		{name:'title' , type:'string'}
	]
});
/**
 *
 * @event addItemCall
 *
 */
Ext.define('app.relatedGridPanel',{
	/*
	 * 'Ext.grid.Panel' has bad renderer in tabs
	 */
	extend:'Ext.Panel',
	alias:'widget.relatedgridpanel',

	dataUrl:false,
	dataGrid:null,
	dataStore:null,
	fieldName:null,

	layout:'fit',

	initComponent:function(){

		this.dataStore = Ext.create('Ext.data.Store',{
			model:'app.relatedGridModel',
			proxy: {
				type: 'ajax',
				reader: {
					type: 'json',
					idProperty: 'id'
				}
			},
			autoLoad:false
		});

		this.columns = [
			{
				sortable: false,
				text: appLang.STATUS,
				dataIndex: 'published',
				width:50,
				align:'center',
				renderer:function(value, metaData, record, rowIndex, colIndex, store){
					if(record.get('deleted')){
						metaData.attr = 'style="background-color:#000000;white-space:normal;"';
						return '<img src="'+app.wwwRoot+'i/system/trash.png" data-qtip="'+appLang.INSTANCE_DELETED+'" >';
					}else{
						return app.publishRenderer(value, metaData, record, rowIndex, colIndex, store);
					}
				}
			},{
				sortable: false,
				text: appLang.TITLE,
				flex:2,
				dataIndex: 'title'
			},
			app.sotrColumn()
		];


		this.tbar = [
			{
				text:appLang.ADD_ITEM,
				listeners:{
					'click':{
						fn:function(){
							this.fireEvent('addItemCall');
						},
						scope:this
					}
				}
			}
		];


		this.dataGrid = Ext.create('Ext.grid.Panel',{
			store:this.dataStore,
			frame: false,
			loadMask:true,
			columnLines: true,
			autoScroll:true,
			enableHdMenu:false,
			columns:this.columns
		});

		this.items = [this.dataGrid];

		this.callParent();


	},
	/**
	 * Load grid data
	 * @param {Array} data
	 */
	setData: function(data){
		this.dataStore.removeAll();
		if(!Ext.isEmpty(data)){
			this.dataStore.loadData(data);
		}
	},
	addRecord:function(record){

		if(this.dataStore.findExact('id',record.get('id'))!=-1){
			return;
		}

		var rPubblished = true;

		if(record.get('published')!=undefined){

			rPubblished = record.get('published');
		}

		var r = Ext.create('app.relatedGridModel', {
			id: record.get('id'),
			title:record.get('title'),
			deleted:0,
			published:rPubblished
		});

		this.dataStore.insert(this.dataStore.getCount(), r);

	},
	getStore:function(){
		return this.dataGrid.getStore();
	},
	getGrid: function(){
		return this.dataGrid;
	},
	collectData: function(){
		var recordList = [];
		this.dataStore.each(function(record){
			if(!record.get('deleted'))
				recordList[recordList.length] = record.get('id');
		});
		var result = {};
		if(recordList.length){
			result[this.fieldName+'[]'] = recordList;
		}else{
			result[this.fieldName]= '';
		}
		return result;
	}
});