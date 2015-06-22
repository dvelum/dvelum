Ext.ns('app.crud.mediaconfig');

Ext.define('app.crud.mediaconfig.Model', {
	extend: 'Ext.data.Model',
	fields: [
		{name:'code' , type:'string'},
		{name:'width' , type:'integer'},
		{name:'height' , type:'integer'},
		{name:'resize' , type:'string'}
	]
});

Ext.define('app.crud.mediaconfig.Main',{
	extend:'Ext.Panel',
	dataStore:null,
	dataGrid:null,
	searchField:null,
	saveButton:null,
	canEdit:false,
	canDelete:false,
	bodyCls:'formBody',

	constructor: function(config) {
		config = Ext.apply({
			layout:'fit'
		}, config || {});
		this.callParent(arguments);
	},

	initComponent: function(){

		this.saveButton = Ext.create('Ext.Button',{
			hidden:!this.canEdit,
			text:appLang.SAVE,
			iconCls:'saveIcon',
			scope:this,
			handler:this.saveAction
		});

		this.addButton = Ext.create('Ext.Button',{
			hidden:!this.canEdit,
			text:appLang.ADD,
			scope:this,
			handler:this.addAction
		});

		this.recropButton = Ext.create('Ext.Button',{
			hidden:!this.canEdit,
			text:appLang.RECROP,
			scope:this,
			handler:this.recropAction
		});

		this.cellEditing = Ext.create('Ext.grid.plugin.CellEditing', {clicksToEdit: 1});
		this.dataStore = Ext.create('Ext.data.Store' , {
			model:'app.crud.mediaconfig.Model',
			autoLoad:true,
			autoSave:false,
			proxy:{
				type: 'ajax',
				api: {
					read    : app.root + 'list',
					update  : app.root + 'update',
					create	: app.root + 'update',
					destroy : app.root + 'delete'
				},
				reader: {
					type: 'json',
					rootProperty: 'data',
					idProperty: 'code'
				},
				actionMethods : {
					create : 'POST',
					read   : 'POST',
					update : 'POST',
					destroy: 'POST'
				},
				simpleSortMode: true,
				writer:{
					writeAllFields:true,
					encode: true,
					listful:true,
					rootProperty:'data'
				}
			},
			listeners:{
				exception:app.storeException
			},
			sorters: [{
				property : 'width',
				direction: 'ASC'
			}]
		});


		var columns = [
			{
				text: appLang.CODE,
				dataIndex: 'code',
				width:100,
				editor:{
					xtype:'textfield',
					allowBlank:false,
					vtype:"alpha"
				},
				editable:this.canEdit
			},{
				text:appLang.WIDTH,
				dataIndex:'width',
				align:'left',
				width:50,
				editor:{
					xtype:'numberfield'
				},
				editable:this.canEdit
			},{
				text:appLang.HEIGHT,
				dataIndex: 'height',
				width:50,
				editor:{
					xtype:'numberfield'
				},
				editable:this.canEdit
			},{
				text:appLang.RESIZE,
				dataIndex: 'resize',
				width:80,
				align:'center',
				editor:{
					xtype:'combo',
					queryMode:'local',
					displayField:'title',
					valueField:'id',
					store:Ext.create('Ext.data.Store',{
						model:'app.comboStringModel',
						remoteSort:false,
						proxy: {
							type: 'ajax',
							simpleSortMode: true
						},
						data:[
							{id:'crop' , title: appLang.CROP},
							{id:'downsizing' , title: appLang.DOWNSIZE}
						]
					})
				},
				editable:this.canEdit,
				renderer:function(value)
				{
					switch(value)
					{
						case 'crop': return appLang.CROP;
							break;
						case 'downsizing': return appLang.DOWNSIZE;
							break;
					}
					return '';
				}
			}
		];

		if(this.canDelete){
			columns.push({
				xtype:'actioncolumn',
				align:'center',
				width:30,
				items:[{
					iconCls:'deleteIcon',
					tooltip:appLang.DELETE_ITEM,
					width:30,
					handler:function(grid, rowIndex, colIndex){
						var rec = grid.getStore().getAt(rowIndex);
						grid.getStore().remove(rec);
					},
					scope:this
				}]
			});
		}


		this.dataGrid = Ext.create('Ext.grid.Panel',{
			store: this.dataStore,
			viewConfig:{
				stripeRows:true
			},
			frame: false,
			loadMask:true,
			columnLines: true,
			scrollable:true,
			selModel: {
				selType: 'cellmodel'
			},
			columns: columns,
			plugins: [this.cellEditing]
		});
		if(this.canEdit){
			this.tbar = [this.addButton,'-',this.recropButton,'-',this.saveButton];
		}
		this.items = [this.dataGrid];
		this.callParent(arguments);
	},
	saveAction:function(){
		this.dataStore.save();
	},
	addAction:function(){
		var r = Ext.create('app.crud.mediaconfig.Model', {
			code:'',
			width:100,
			height:100,
			resize:'crop'
		});
		this.dataStore.insert(0, r);
		r.setDirty();
		this.cellEditing.startEditByPosition({row: 0, column: 0});
	},
	recropAction:function(){
		var imageSizes = {};

		this.dataStore.each(function(record){
			imageSizes[record.get('code')] = [record.get('width') , record.get('height')];
		},this);

		var win = Ext.create('app.crud.mediaconfig.CropWindow',{
			title:appLang.RECROP,
			sizeList:imageSizes
		});
		win.show();
	}
});

/**
 * Edit window for ORM object Index
 */
Ext.define('app.crud.mediaconfig.CropWindow', {

	extend: 'Ext.window.Window',
	dataForm:null,
	sizeList:'',

	constructor: function(config) {
		config = Ext.apply({
			modal: true,
			layout:'fit',
			width: app.checkWidth(350),
			height:app.checkHeight(300),
			closeAction: 'destroy',
			maximizable:true
		}, config || {});

		this.callParent(arguments);
	},

	/**
	 * @todo fix columns menu
	 */
	initComponent:function(){
		var groupItems = [];

		for (index in  this.sizeList){
			if(typeof i == 'function' || app.imageSize[index] == undefined){
				continue;
			}
			groupItems.push({name:"size[]" , boxLabel:index +' ('+this.sizeList[index][0]+'x'+this.sizeList[index][1]+')' , inputValue:index });
		}

		this.dataForm = Ext.create('Ext.form.Panel',{
			bodyPadding:5,
			scrollable:true,
			bodyCls:'formBody',
			items:[
				{
					xtype:'checkbox',
					name:'notcroped',
					boxLabel:appLang.MSG_RECROP_ONLY_AUTOCROPED,
					checked:true
				},{
					xtype:'checkboxgroup',
					columns:1,
					width:250,
					items:groupItems
				}
			]
		});

		this.buttons = [
			{
				text:appLang.START,
				scope:this,
				handler:this.cropAction
			},{
				text:appLang.CANCEL,
				scope:this,
				handler:this.close
			}
		];

		this.items = [this.dataForm];
		this.callParent(arguments);
	},
	cropAction:function(){
		this.dataForm.getForm().submit({
			clientValidation: true,
			waitMsg:appLang.SAVING,
			method:'post',
			url:app.root + 'startcrop',
			success: function(form, action) {
				if(!action.result.success){
					Ext.Msg.alert(appLang.MESSAGE, action.result.msg);
				} else{

				}
			},
			failure: app.formFailure
		});

		if(!app.taskWindow){
			setTimeout(function(){
				var url = app.createUrl([app.admin , 'tasks']);
				app.taskWindow = window.open(url,'taskWindow','toolbar=0,status=0,menubar=0');
			},2000);
		}else{
			setTimeout(function(){
				app.taskWindow.focus();
			},2000);
		}
	}
});


Ext.onReady(function(){
	Ext.QuickTips.init();
	var dataPanel = Ext.create('app.crud.mediaconfig.Main',{
		title:appLang.MODULE_MEDIACONFIG,
		canEdit:canEdit,
		canDelete:canDelete
	});
	app.content.add(dataPanel);
});
