Ext.ns('app.crud.compiller');

Ext.define('app.crud.compiller.Model', {
    extend: 'Ext.data.Model',
    fields: [
         {name:'id'},
         {name:'name'},
 	     {name:'files_count' , type:'integer'},
 	     {name:'size' , type:'string'},
 	     {name:'active', type:'boolean'},
 	     {name:'valid', type:'boolean'}
    ]
});
Ext.define('app.crud.compiller.RecordModel', {
    extend: 'Ext.data.Model',
    fields: [
        {name:'id' ,  type:'string'},
        {name:'title' , type:'string'},
        {name:'class_name', type:'string'}
    ]
});

/**
 *
 *  @event packageSaved
 *
 */
Ext.define('app.crud.compiller.AddPackageWindow', {
	extend:'Ext.window.Window',

	dataForm:null,

	controllerUrl:null,

	constructor: function(config) {
		config = Ext.apply({
			layout:'fit',
			modal:true,
			bodyCls:'formBody',
			width: app.checkWidth(300),
	        height:app.checkHeight(120),
	        closeAction: 'destroy',
	        resizable:false,
	        title:appLang.ADD_PACKAGE
	    }, config || {});
		this.callParent(arguments);
	},

	initComponent:function(){
		this.dataForm = Ext.create('Ext.form.Panel',{
			border:false,
    		bodyPadding: 15,
    		bodyCls:'formBody',
		    layout: 'anchor',
		    defaults: {
		        anchor: '100%',
		        labelWidth:50
		    },
			items:[{
				xtype:'textfield',
				fieldLabel:appLang.NAME,
	            name:'name',
	            allowBlank:false,
	            vtype:"alphanum"
			}]
		});

		this.buttons = [{
			text:appLang.SAVE,
			scope:this,
			handler:this.savePackage
		}];

		this.items = [this.dataForm];
		this.callParent(arguments);
	},
	savePackage:function(){
		this.dataForm.getForm().submit({
			clientValidation: true,
			waitMsg:appLang.SAVING,
			method:'post',
			scope:this,
			url:this.controllerUrl + 'addpackage',
			success: function(form, action) {
   		 		if(!action.result.success){
   		 			Ext.Msg.alert(appLang.MESSAGE, action.result.msg);
   		 		} else{
   		 			this.fireEvent('packageSaved');
   		 			this.close();
   		 		}
   	        },
   	        failure: app.formFailure
   	    });
	}
});
/**
 *
 * @event filesAdded
 *
 */
Ext.define('app.crud.compiller.AddPackageItemWin', {
	extend:'Ext.Window',
	width:300,
	height:500,

	itemForm:null,

	controllerUrl:'',
	curPackage:null,

	initComponent:function(){

		this.layout = 'fit',
		this.modal = true,

		this.fileTree = Ext.create('Ext.tree.Panel',{
			rootVisible:false,
	        useArrows: true,
	        autoScrolle:true,
	        border:false,
	        store:Ext.create('Ext.data.TreeStore',{
				proxy: {
				        type: 'ajax',
				    	url:this.controllerUrl + 'fslist',
				    	reader: {
				            type: 'json',
				            idProperty: 'id'
				        }
				},
				root: {
				        text: '/',
				        expanded: true,
				        id:'.'
				}
			})
		});
		this.fileTree.on('checkchange',app.checkChildNodes,this);

		this.items = [this.fileTree];

		this.buttons = [{
			text:appLang.ADD,
			scope:this,
			handler:this.addItem
		},{
			text:appLang.CLOSE,
			scope:this,
			handler:this.close
		}];

		this.callParent(arguments);
	},
	addItem:function(){
		var checked = this.fileTree.getChecked();
		if(!checked.length){
			Ext.Msg.alert(appLang.MESSAGE, appLang.NTD);
			return;
		}
		var paths = [];
		Ext.each(checked,function(item){
			if(item.get('leaf')){
				paths.push(item.get('id'));
			}
		},this);
		this.setLoading(true);
		Ext.Ajax.request({
			url:this.controllerUrl + 'addrecords',
			method: 'post',
			scope:this,
			params:{
				'paths[]':paths,
				'package':this.curPackage
			},
	 		success: function(response, request) {
	 			response =  Ext.JSON.decode(response.responseText);
	 			if(response.success){
	 				this.setLoading(false);
	 				this.fireEvent('filesAdded');
	 				this.close();
	 			}else{
	 				Ext.Msg.alert(appLang.MESSAGE , response.msg);
	 				this.setLoading(false);
	 			}
	 		},
	 		failure:app.ajaxFailure
		});
	}
});

Ext.define('app.crud.compiller.Main',{
	extend:'Ext.panel.Panel',

	packageStore:null,
	packageGrid:null,
	cellEditing:null,

	recordsStore:null,
	recordsGrid:null,

	addPackageButton:null,

	addRecordButton:null,

	canEdit:false,
	canDelete:false,

	controllerUrl:null,

	curPackage:null,

	searchPanel:null,

	constructor: function(config) {
		config = Ext.apply({
			layout:'border'
	    }, config || {});
		this.callParent(arguments);
	},

	initComponent: function(){

		this.cellEditing = Ext.create('Ext.grid.plugin.CellEditing', {clicksToEdit: 2});

		this.addPackageButton = new Ext.create('Ext.button.Button',{
			text:appLang.ADD_PACKAGE,
			scope:this,
			handler:this.showAddWin
		});

		this.addRecordButton = new Ext.create('Ext.button.Button',{
			text:appLang.ADD_ITEM,
			scope:this,
			handler:this.showAddRecordsWin,
			disabled:true
		});

		this.searchPanel = Ext.create('SearchPanel',{
			fieldNames:['class_name', 'title'],
			local:true,
			width:130,
			hideLabel:true
		});

		this.packageStore = Ext.create('Ext.data.Store' , {
		    model:'app.crud.compiller.Model',
		 	autoLoad:true,
		 	autoSave:false,
			proxy:{
				type: 'ajax',
				url:this.controllerUrl + 'listpackages',
			    reader: {
		            type: 'json',
					rootProperty: 'data',
		            idProperty: 'id'
		        },
		    	simpleSortMode: true,
				listeners:{
					'scope':this,
					'exception':function(){
						Ext.Msg.alert(appLang.MESSAGE , appLang.STORE_TIMING_ERROR);
					}
				}
			},
			sorters: [{
                  property : 'name',
                  direction: 'ASC'
            }]
		});

		var columns = [{
			text: appLang.NAME,
			dataIndex: 'name',
			flex:1,
			align:'left',
			editor:{
				xtype:'textfield',
				allowBlank:false,
	            vtype:"alphanum"
			},
			editable:this.canEdit
		},{
			text:appLang.FILES_COUNT,
			dataIndex:'files_count',
			width:87,
			align:'right'
		},{
			text:appLang.SIZE_MB,
			dataIndex:'size',
			width:67,
			align:'center'
		},{
		    text: appLang.ACTIVE,
			dataIndex: 'active',
			width:60,
			align:'center',
			id:'active',
			renderer:app.checkboxRenderer,
			editor:{
	        	xtype:'checkbox'
	        },
	        editable:this.canEdit
		},{
		    text: appLang.VALID_PACKAGE,
			dataIndex: 'valid',
			width:80,
			align:'center',
			id:'valid',
			renderer:app.checkboxRenderer
		}];

		if(this.canEdit){
			columns.push({
				xtype:'actioncolumn',
	            width:30,
	            align:'center',
	            items: [{
	                iconCls: 'buildIcon',
	                tooltip: appLang.REBUILD_PACKAGE,
	                handler:this.rebuildPackage,
	                scope:this
	            }]
			});

		}

		if(this.canDelete){
			columns.push({
				xtype:'actioncolumn',
	            width:30,
	            align:'center',
	            items: [{
	                iconCls: 'deleteIcon',
	                tooltip: appLang.DELETE,
	                handler:this.deletePackage,
	                scope:this
	            }]
			});
		}
		var plugins=[];
		if(this.canEdit){
			plugins.push(this.cellEditing);
		}

		this.packageGrid = Ext.create('Ext.grid.Panel',{
			store: this.packageStore,
			viewConfig:{
				stripeRows:true
			},
			frame: false,
			layout:'fit',
		    loadMask:true,
		    region:'center',
		    split:true,
			columnLines: true,
			autoScroll:true,
		    columns:columns,
		 	plugins:plugins
		});

		this.packageGrid.on('edit', function(editor, e) {
			this.savePackagesAction();
		},this);

		this.packageGrid.on('selectionchange',this.onSelectionChange,this);

		if(this.canEdit){
			this.packageGrid.addDocked([{
		        xtype: 'toolbar',
		        dock: 'top',
		        items: [this.addPackageButton]
		    }]);
		}

		this.recordsStore = Ext.create('Ext.data.Store' , {
		    model:'app.crud.compiller.RecordModel',
		 	autoLoad:false,
		 	autoSave:false,
			proxy:{
				type: 'ajax',
				url:this.controllerUrl + 'listrecords',
			    reader: {
		            type: 'json',
					rootProperty: 'data',
		            idProperty: 'id'
		        },
				listeners:{
					'scope':this,
					'exception':function(){
						Ext.Msg.alert(appLang.MESSAGE , appLang.STORE_TIMING_ERROR);
					}
				}
			}
		});
		var me = this;
		var bufferedSave = Ext.Function.createBuffered(me.saveOrder,1200, me);
		this.searchPanel.store = this.recordsStore;

		var col = [{
			text: appLang.CLASS_NAME,
			dataIndex: 'class_name',
			flex:2,
			sortable:false,
			align:'left'
		},{
			text: appLang.NAME,
			dataIndex: 'title',
			sortable:false,
			flex:2,
			align:'left'
		},
		{
	    	xtype:'actioncolumn',
	    	width:40,
	    	tooltip:appLang.SORT,
	    	dataIndex:'id',
	    	items:[
	    	       {
	    	    	   iconCls: 'downIcon',
	    	    	   handler:function(grid, rowIndex, colIndex){
	    	    		   var total = grid.getStore().getCount();
	    	    		   if(rowIndex == total - 1)
	    	    			   return;

	    	    		   var sRec = grid.getStore().getAt(rowIndex);
	    	    		   grid.getStore().removeAt(rowIndex);
	    	    		   grid.getStore().insert(rowIndex+1 , sRec);
	    	    		   bufferedSave();

	    	    	  }
	    	       },{
	    	    	   iconCls: 'upIcon',
	    	    	   handler:function(grid, rowIndex, colIndex){
	    	    		   var total = grid.getStore().getCount();
	    	    		   if(rowIndex == 0)
	    	    			   return;

	    	    		   var sRec = grid.getStore().getAt(rowIndex);
	    	    		   grid.getStore().removeAt(rowIndex);
	    	    		   grid.getStore().insert(rowIndex -1 , sRec);
	    	    		   bufferedSave();
	    	    	   }
	    	       }
	    	]
	    }
		];
		var me = this;
		if(this.canDelete){
			col.push({
				xtype:'actioncolumn',
	            width:30,
	            align:'center',
	            items: [{
	                iconCls: 'deleteIcon',
	                tooltip: appLang.DELETE,
	                handler:me.deleteRecord,
	                scope:me
	            }]
			});
		}

		this.recordsGrid = Ext.create('Ext.grid.Panel',{
			store: this.recordsStore,
			viewConfig:{
				stripeRows:true
			},
			frame: false,
		    loadMask:true,
			columnLines: true,
			autoScroll:true,
		    columns:col,
		    split:true,
		    width:500
		});
		var toolbarItems = [];
		if(this.canEdit){
			toolbarItems.push(this.addRecordButton);
		}
		//toolbarItems.push('->');
		//toolbarItems.push(this.searchPanel);
		this.recordsGrid.addDocked([{
	        xtype:'toolbar',
	        dock:'top',
	        items:toolbarItems
	    }]);

		this.items = [this.packageGrid,{
			xtype:'panel',
			border:false,
			region:'east',
			layout:'fit',
			split:true,
			items:[this.recordsGrid]
		}];

		this.tbar = [{
			text:appLang.COMPILE_PACKAGES,
			scope:this,
			handler:this.compilePackages
		},'-',{
			text:appLang.COMPILE_LANG,
			scope:this,
			handler:this.compileLang
		},'-',{
            text:appLang.REBUILD_CLASS_MAP,
            scope:this,
            handler:this.rebuildClassMap
           // icon:app.wwwRoot + "i/system/map.png"
        }];

		this.callParent(arguments);
	},
	/**
	 * Compile all packages
	 */
	compilePackages:function(){
		this.setLoading(true);
		Ext.Ajax.request({
			url:this.controllerUrl + 'rebuildall',
			method: 'post',
			scope:this,
	 		success: function(response, request) {
	 			response =  Ext.JSON.decode(response.responseText);
	 			if(response.success){
	 				this.setLoading(false);
	 				this.packageStore.load();
	 			}else{
	 				Ext.Msg.alert(appLang.MESSAGE , response.msg);
	 				this.setLoading(false);
	 			}
	 		},
	 		failure:app.ajaxFailure
		});
	},
	/**
	 * REbuild package
	 * @param {Ext.grid.Panel} grid
	 * @param integer rowIndex
	 * @param integer colIndex
	 */
	rebuildPackage:function(grid, rowIndex, colIndex){
		this.setLoading(true);
		Ext.Ajax.request({
			url:this.controllerUrl + 'rebuildpackage',
			method: 'post',
			scope:this,
			params:{
				name:grid.getStore().getAt(rowIndex).get('name')
			},
	 		success: function(response, request) {
	 			response =  Ext.JSON.decode(response.responseText);
	 			if(response.success){
	 				this.setLoading(false);
	 				this.packageStore.load();
	 			}else{
	 				Ext.Msg.alert(appLang.MESSAGE , response.msg);
	 				this.setLoading(false);
	 			}
	 		},
	 		failure:app.ajaxFailure
		});
	},
	compileLang:function(){
		this.setLoading(true);
		Ext.Ajax.request({
			url:this.controllerUrl + 'lang',
			method: 'post',
			scope:this,
	 		success: function(response, request) {
	 			response =  Ext.JSON.decode(response.responseText);
	 			if(response.success){
	 				this.setLoading(false);
	 			}else{
	 				Ext.Msg.alert(appLang.MESSAGE , response.msg);
	 				this.setLoading(false);
	 			}
	 		},
	 		failure:app.ajaxFailure
		});
	},
    rebuildClassMap:function()
    {
        this.setLoading(true);
        Ext.Ajax.request({
            url:this.controllerUrl + 'rebuildmap',
            method: 'post',
            scope:this,
            success: function(response, request) {
                response =  Ext.JSON.decode(response.responseText);
                if(response.success){
                    this.setLoading(false);
                }else{
                    Ext.Msg.alert(appLang.MESSAGE , response.msg);
                    this.setLoading(false);
                }
            },
            failure:app.ajaxFailure
        });
    },
	onSelectionChange:function(selmodel, selected){
		this.searchPanel.searchField.reset();
		if(!selected.length){
			return;
		}
		this.addRecordButton.enable();
		this.curPackage = selected[0].get('id');

		this.recordsStore.proxy.setExtraParam('package',this.curPackage);
		this.recordsStore.load();
		this.recordsGrid.updateLayout();
		this.recordsGrid.doLayout();
		this.updateLayout();
		this.doLayout();
	},
	showAddRecordsWin:function(){
		var win = Ext.create('app.crud.compiller.AddPackageItemWin',{
			title:appLang.ADD_ITEM,
			controllerUrl:this.controllerUrl,
			curPackage:this.curPackage
		});

		win.on('filesAdded',function(){
			this.recordsStore.load();
			this.packageStore.load();
			this.recordsGrid.updateLayout();
			this.updateLayout();
		},this);

		win.show();
	},
	deleteRecord:function(grid, rowIndex, colIndex, item, eventObj){
		var value = grid.getStore().getAt(rowIndex).get('title');
		var me = this;
		Ext.Msg.confirm(appLang.CONFIRM, appLang.MSG_CONFIRM_DELETE +' "'+value+'"?', function(btn){
			if(btn != 'yes'){
				return;
			}
			Ext.Ajax.request({
				url:me.controllerUrl + 'removerecord',
				method: 'post',
				scope:me,
				params:{
					'value':value,
					'package':me.curPackage
				},
		 		success: function(response, request) {
		 			response =  Ext.JSON.decode(response.responseText);
		 			if(response.success){
		 				me.packageStore.load();
		 				me.recordsStore.removeAt(rowIndex);
		 			}else{
		 				Ext.Msg.alert(appLang.MESSAGE , response.msg);
		 			}
		 		},
		 		failure:app.ajaxFailure
			});
		},this);
	},
	showAddWin:function(){
		var win = Ext.create('app.crud.compiller.AddPackageWindow',{
			controllerUrl:this.controllerUrl
		});

		win.on('packageSaved',function(){
			this.packageStore.load();
			this.addRecordButton.disable();
		},this);

		win.show();
	},
	savePackagesAction:function(){
		var data = app.collectStoreData(this.packageStore, true);

		if(!data.length){
			return;
		}

		data = Ext.encode(data);
		Ext.Ajax.request({
			url:this.controllerUrl + 'updatepackages',
			method: 'post',
			scope:this,
			params:{
				data:data
			},
	 		success: function(response, request) {
	 			response =  Ext.JSON.decode(response.responseText);
	 			if(response.success){
	 				this.packageStore.load();
	 				this.addRecordButton.disable();
	 			}else{
	 				Ext.Msg.alert(appLang.MESSAGE , response.msg);
	 			}
	 		},
	 		failure:app.ajaxFailure
		});
	},
	deletePackage:function(grid, rowIndex, colIndex, item, eventObj){
		var id = grid.getStore().getAt(rowIndex).get('id');
		Ext.Msg.confirm(appLang.CONFIRM, appLang.MSG_CONFIRM_DELETE_PACKAGE +' "'+id+'"?', function(btn){
			if(btn != 'yes'){
				return;
			}
			Ext.Ajax.request({
				url:this.controllerUrl + 'removepackage',
				method: 'post',
				scope:this,
				params:{
					name:id
				},
		 		success: function(response, request) {
		 			response =  Ext.JSON.decode(response.responseText);
		 			if(response.success){
		 				this.packageStore.removeAt(rowIndex);
		 				if(id == this.curPackage){
		 					this.addRecordButton.disable();
		 					this.recordsStore.removeAll();
		 				}
		 			}else{
		 				Ext.Msg.alert(appLang.MESSAGE , response.msg);
		 			}
		 		},
		 		failure:app.ajaxFailure
			});
		},this);
	},
	saveOrder:function(){
		var order = [];
		this.recordsStore.each(function(record){
			order.push(record.get('title'));
		});

		Ext.Ajax.request({
			url:this.controllerUrl + 'saveorder',
			method: 'post',
			scope:this,
			params:{
				name:this.curPackage,
				'order[]':order

			},
	 		success: function(response, request) {
	 			response =  Ext.JSON.decode(response.responseText);
	 			if(response.success){

	 			}else{
	 				Ext.Msg.alert(appLang.MESSAGE , response.msg);
	 			}
	 		},
	 		failure:app.ajaxFailure
		});
	}
});

Ext.onReady(function(){
	var dataPanel = Ext.create('app.crud.compiller.Main',{
		title:appLang.COMPILLER + ' :: ' + appLang.HOME,
		canEdit:canEdit,
		canDelete:canDelete,
		controllerUrl:app.root
	});

	app.content.add(dataPanel);
});
