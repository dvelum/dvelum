/**
 *
 * @event select
 *
 */
Ext.define('app.crud.orm.DataViewWindow', {
	extend:'Ext.window.Window',
	objectName:'',
	controllerUrl:'',
	width:800,
	height:600,
	maximizable:true,
	readOnly:false,
	layout:'fit',
	
	dataGrid:null,
	dataStore:null,
	searchField:null,
	isVc:null,
	primaryKey:'id',
	
	editorCfg:false,
	selectMode:false,
	closeOnSelect:true,
	
	modal:true,
	relatedGrids:null,
	
	initComponent:function(){	
		
		if(this.selectMode){
			this.buttons = [{
		    	 text:appLang.SELECT,
		    	 scope:this,
		    	 handler:this.selectItem
		     },{
		    	 text:appLang.CLOSE,
		    	 scope:this,
		    	 handler:this.close
		     }];
		}
		
		this.callParent();
		
		this.on('show',function(){
			app.checkSize(this);
			this.loadInterface();
		},this);
	},
	loadInterface:function(){
		var me = this;
		me.getEl().mask(appLang.LOADING);
		Ext.Ajax.request({
			url:this.controllerUrl + 'viewconfig',
			method: 'post',
			params:{
				object:this.objectName
			},
			scope:this,
	 		success: function(response, request) {
	 			response =  Ext.JSON.decode(response.responseText);
	 			if(!response.success){
	 				Ext.Msg.alert(appLang.MESSAGE , response.msg);
	 			} else {
	 				this.configurate(response.data);
	 			}
	 			me.getEl().unmask();
	 		},
	 		failure:function(){
	 			me.getEl().unmask();
	 			app.ajaxFailure(arguments);
	 		}
		});
	},
	configurate:function(data){

		this.dataStore =  Ext.create('Ext.data.Store', {
		    fields:data.fields,
		    remoteSort:true,
		    proxy: {
		        type: 'ajax',
		    	url:this.controllerUrl + 'list',
		    	directionParam:"pager[dir]",
	            limitParam:"pager[limit]",
	            simpleSortMode:true,
	            sortParam:"pager[sort]",
	            startParam:"pager[start]",
		    	extraParams:{
		    		object:this.objectName
		    	},
		        reader: {
		        	type:'json',
		            idProperty:"id",
					rootProperty:"data",
	                totalProperty:"count"	
		        },
		        simpleSortMode: true
		    },
		    autoLoad: true
		});
	

		var cols = [];
		
		if(!this.selectMode)
		{
			cols.push(
		            {
		            	xtype:'actioncolumn',
		            	width:30,
		            	items:[
		            	       {
		            	    	   iconCls:'editIcon',
		            	    	   scope:this,
		            	    	   tooltip:appLang.EDIT,
		            	    	   handler:function(grid, rowIndex, colIndex){
		            	    		   var rec = grid.getStore().getAt(rowIndex);
		            	    		   this.showEdit(rec.get('id'));
		            	    	   }
		            	       }
		            	]
		       });
		}
		
		Ext.each(data.columns , function(item){
			cols.push(item);
		});

		var tBar = [];
		
		if(!this.selectMode && !this.readOnly)
		{
			tBar.push({
		    	  text:appLang.ADD_ITEM,
		    	  scope:this,
		    	  handler:function(){this.showEdit(0);}
		      });
		}

		this.searchField = Ext.create('SearchPanel',{
			store:this.dataStore,
			isLocal:false,
			fieldNames:data.searchFields
		});
		
		tBar.push('->', this.searchField);

		this.dataGrid = Ext.create('Ext.grid.Panel',{
			columns:cols,
			selModel:Ext.create('Ext.selection.RowModel',{mode:'single'}),
			columnLines:true,
			store:this.dataStore,
			loadMask:true,
			tbar:tBar,
			viewConfig:{
				enableTextSelection: true
			},
			bbar:Ext.create("Ext.PagingToolbar", {
		        store: this.dataStore,
		        displayInfo: true,
		        displayMsg: appLang.DISPLAYING_RECORDS + " {0} - {1} " + appLang.OF + " {2}",
		        emptyMsg:appLang.NO_RECORDS_TO_DISPLAY
		    })
		});
		
		if(this.selectMode){
			this.dataGrid.on('celldblclick',function(table, td, cellIndex, record, tr, rowIndex, e, eOpts ){
				this.fireEvent('select',record);
				if(this.closeOnSelect){
					this.close();
				}
    		},this);	
		}else{
			this.dataGrid.on('celldblclick',function(table, td, cellIndex, record, tr, rowIndex, e, eOpts ){
    			this.showEdit(record.get(this.primaryKey));
    		},this);
		}
		this.add(this.dataGrid);
	},
	selectItem:function()
	{
		var sm = this.dataGrid.getSelectionModel();
		if(!sm.hasSelection()){
			Ext.Msg.alert(appLang.MESSAGE,appLang.MSG_SELECT_ITEM_FOR_ADDING);
			return;
		}
		this.fireEvent('select',sm.getSelection()[0]);
		if(this.closeOnSelect){
			this.close();		
		}
	},
	showEdit:function(id)
	{
		if(!this.editorCfg)
		{
			var me = this;
			me.getEl().mask(appLang.LOADING);
			Ext.Ajax.request({
				url:this.controllerUrl + 'editorconfig',
				method: 'post',
				params:{
					object:this.objectName
				},
				scope:this,
		 		success: function(response, request) {
		 			response =  Ext.JSON.decode(response.responseText);
		 			if(!response.success){
		 				Ext.Msg.alert(appLang.MESSAGE , response.msg);
		 			} else {
		 				me.editorCfg = response.data;
		 				me.createEditWindow(id);
		 			}
		 			me.getEl().unmask();
		 		},
		 		failure:function(){
		 			me.getEl().unmask();
		 			app.ajaxFailure(arguments);
		 		}
			});
		}else{
			this.createEditWindow(id);
		}
	},
	createEditWindow:function(id)
	{
		var win;
		var me = this;		
		var related = this.editorCfg.related;
		var fields = Ext.JSON.decode(this.editorCfg.fields);

		this.relatedGrids = [];
				
		if(!Ext.isEmpty(related)){
			Ext.each(related , function(item){	
				var grid = Ext.create('app.relatedGridPanel',{
					title:item.title,
					fieldName:item.field,	
					listeners:{
						addItemCall: {
							fn:function(){
								Ext.create('app.crud.orm.DataViewWindow', {
						            width:600,
						            height:500,
						            selectMode:true,
						            closeOnSelect:false,
						            objectName:item.object,
						            controllerUrl:this.controllerUrl,
						            isVc:this.isVc,
						            title:item.title,
						            readOnly:this.editorCfg.readOnly,
						            primaryKey:this.editorCfg.primaryKey,
						            listeners: {
						                scope: this,
						                select:function(record){
						                	if(record.get('published')!= undefined){
						                		published = record.get('published');
						                	}else{
												published = 1;
											}
						                	me.relatedGrids[item.field].addRecord(app.relatedGridModel.create({
						                		'id':record.get('id'),
						                		'published':published,
						                		'title':record.get(item.titleField),
						                		'deleted':0
						                	}));
						                }
						            }
						        }).show();	
							},
							scope:this
						}
					}
				});			
				this.relatedGrids[item.field] = grid;
				fields.push(grid);
			},this);
		}
	
		if(this.isVc){
			win = Ext.create('app.contentWindow',{
				width:800,
				height:800,
				objectName:this.objectName,
			    hasPreview:false,
				items:fields,
				dataItemId:id,
				primaryKey:this.primaryKey,
				controllerUrl:this.controllerUrl + app.createUrl(['editorvc','']),
				canEdit:!this.readOnly,
				canDelete:!this.readOnly,
				canPublish:!this.readOnly,
				listeners:{
					dataSaved:{
						fn:function(){
							me.dataStore.load();
						},
						scope:me
					}
				}
			});

		}else{
			win = Ext.create('app.editWindow',{
				width:800,
				height:800,
				dataItemId:id,
				canEdit:!this.readOnly,
				canDelete:!this.readOnly,
				primaryKey:this.primaryKey,
				items:fields,
				objectName:this.objectName,
				controllerUrl:this.controllerUrl + app.createUrl(['editor','']),
				listeners:{
					dataSaved:{
						fn:function(){
							me.dataStore.load();
						},
						scope:me
					}
				}
			});
		}
		win.show();
	}
});