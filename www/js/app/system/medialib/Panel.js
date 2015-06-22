Ext.ns('app.medialib');

app.medialib.typesStore = Ext.create('Ext.data.Store',{
	model:'app.comboStringModel',
	data:[
	      {id:"", title:appLang.ALL},
	      {id:"file", title:appLang.FILE},
	      {id:"image", title:appLang.IMAGE},
	      {id:"audio", title:appLang.AUDIO},
	      {id:"video", title:appLang.VIDEO}
	]
});

/**
 * Media library panel component
 * @author Kirill Egorov 2011
 * @extend Ext.Panel
 *
 * @event rightsChecked
 *
 * @event createPanels
 */
Ext.define('app.medialibPanel',{
		extend:'Ext.Panel',
	   /**
	    * @var {Ext.grid.GridPanel}
	    */
	   dataGrid:null,
	   /**
	    * @var {Ext.data.JsonStore}
	    */
	   dataStore:null,
	   /**
	    * @var {Ext.form.FormPanel}
	    */
	   dataPropertiesForm:null,
	   /**
	    * @var {Ext.Panel}
	    */
	   dataProperties:null,
	   /**
	    * @var {Ext.Panel}
	    */
	   dataCatalog:null,
	   /**
	    * @var {Ext.tab.Panel}
	    */
	   dataTabs:null,
	   /**
	    * @var {app.medialib.CategoryTree}
	    */
	   dataTree:null,

	   searchField:null,

	   srcTypeFilter:null,

	   selectedCategory:0,

	   checkRights:false,
	   canEdit:false,
	   canDelete:false,
	   canView:true,

	   addFilesBtn:null,

	   constructor: function(config) {
			config = Ext.apply({
				layout:'border',
				tbar: new Ext.Panel({
					border:false,
					bodyBorder:false,
					items:[]
				})
		    }, config || {});
    		this.callParent(arguments);
	   },
	   getRights:function(){
		    var me = this;

			Ext.Ajax.request({
					url: app.admin + app.delimiter + 'medialib' +  app.delimiter + 'rights',
		    		method: 'post',
		    		timeout:240000,
		    		success: function(response, request) {
		    			response =  Ext.JSON.decode(response.responseText);
		    			if(response.success){
		    				me.canEdit = response.data.canEdit;
		    				me.canDelete = response.data.canDelete;
		    			}else{
		    				me.canView = false;
		    			}
		    			me.onRightsChecked();
		    	  },
		          failure:function(){
		        	me.canView = false;
		        	me.onRightsChecked();
		          	Ext.Msg.alert(appLang.MESSAGE, appLang.CANT_EXEC);
		          }
		    });
	   },
	   initComponent:function(){
		   this.callParent();

		   if(this.checkRights){
			   this.getRights();
		   }else{
			   this.onRightsChecked();
		   }
	   },
	   onRightsChecked:function(){
		   this.createPanels();
		   this.fireEvent('rightsChecked');
	   },
	   initMainStore:function()
	   {
		   this.dataStore = Ext.create('Ext.data.Store', {
			    model: 'app.medialibModel',
			    proxy: {
			        type: 'ajax',
			        url: app.admin + app.delimiter + 'medialib' +  app.delimiter + 'list',
			        reader: {
			            type: 'json',
						rootProperty: 'data',
			            totalProperty: 'count',
			            idProperty: 'id'
			        },
			        startParam:'pager[start]',
			        limitParam:'pager[limit]',
			        sortParam:'pager[sort]',
			        directionParam:'pager[dir]',
				    simpleSortMode: true
			    },
			    pageSize: 30,
		        remoteSort: true,
			    autoLoad: false,
			    sorters: [{
	                  property : 'date',
	                  direction: 'DESC'
	            }]
			});
	   },
	   createPanels:function()
	   {
			this.initMainStore();

			this.srcTypeFilter = Ext.create('Ext.form.ComboBox',{
					displayField:"title",
					queryMode:"local",
					forceSelection:true,
					store: app.medialib.typesStore,
					triggerAction:"all",
					valueField:"id",
					allowBlank: false,
					value :"",
					width: 150
			});

			var handle = this;

			var columnConfig = [];

			if(this.canEdit){
				columnConfig.push(
						{
			            	xtype:'actioncolumn',
			            	align:'center',
			            	width:30,
			            	items:[
			            	       {
			            	    	   tooltip:appLang.EDIT_RECORD,
			            	    	   iconCls:'editIcon',
			            	    	   width:30,
			            	    	   handler:function(grid, rowIndex, colIndex){
			            	    		   handle.showEdit(grid.getStore().getAt(rowIndex));
			            	    	   }
			            	       }
			            	]
			            }
				);
			}

			columnConfig.push({
					        	 text:appLang.ICON,
					        	 dataIndex:'id',
					        	 width:80,
					        	 align:'center',
					        	 sortable:false,
					        	 xtype:'templatecolumn',
					        	 tpl: new Ext.XTemplate(
					        			   '<div style="white-space:normal;" >',
					        			   		'<img src="{icon}?{modified}" alt="[icon]"  style="border:1px solid #000000;"/>',
					        			   '<div>'
					        	   )
					         });


			columnConfig.push({
					        	 text:appLang.TITLE,
							     dataIndex:'title',
					        	 sortable:true,
					        	 xtype:'templatecolumn',
					        	 flex:1,
					        	 tpl: new Ext.XTemplate(
					        			   '<div style="white-space:normal;" >',
					        			   		'<b>' + appLang.TITLE + ':</b> {title}<br>',
					        			   		'<b>' + appLang.TYPE + ':</b> {type}<br>',
					        			   		'<b>' + appLang.SIZE + ':</b> {size} mb<br>',
					        			   		'<b>' + appLang.UPLOADED_BY + ':</b> {user_name} <br>',
					        			   		'<b>' + appLang.CAPTION + ':</b> {caption} <br>',
					        			   '<div>'
					        	   )
						     });

		  columnConfig.push({
						    	 text:appLang.UPLOAD_DATE,
					        	 width:110,
					        	 dataIndex:'date',
					        	 xtype:'datecolumn',
					        	 sortable:true,
					         	 format:'M d, Y H:i'
					         }
					);

			if(this.canDelete){
				columnConfig.push({
						xtype:'actioncolumn',
						width:20,
						align:'center',
						items:[
						       {
						    	   iconCls:'deleteIcon',
						    	   tooltip:appLang.DELETE,
						    	   scope:this,
						    	   handler:function(grid, rowIndex, colIndex){
						    	   	var record = grid.getStore().getAt(rowIndex);
						    	   	Ext.Msg.confirm(appLang.CONFIRM, appLang.REMOVE_IMAGE + ' ' + record.get('title') + '?' , function(btn){
							   			if(btn != 'yes'){
							   				return false;
							   			}

						    		   	this.deleteItem(grid.getStore().getAt(rowIndex));
						    	   	},this);
						    	   }
						       }
						 ]
				});
			}

			this.searchField = new SearchPanel({
				 store:this.dataStore,
				 local:false
			});

			this.addFilesBtn = Ext.create('Ext.Button',{
				text:appLang.ADD_FILES,
				hidden:!this.canEdit,
				listeners:{
					click:{
						fn:function(){
			  					var win =  Ext.create('app.fileUploadWindow',{
			  						uploadUrl: app.createUrl([app.admin ,'medialib' , 'upload', this.selectedCategory])
			  					});
			  					win.on('filesuploaded',function(){
			  						this.dataStore.load();
			  					},this);
			  					win.show();
						},
						scope:this
					}
				}
			});

			this.dataGrid = Ext.create('Ext.grid.Panel',{
				store:this.dataStore,
				region:'center',
				selModel:{mode:'MULTI'},
				viewConfig:{
					stripeRows:true,
					plugins: {
			                ptype: 'gridviewdragdrop',
			                dragGroup:'medialibraryItem',
				        	enableDrag:this.canEdit
			        }
				},
				tbar:[
					this.addFilesBtn,'-',
					appLang.MEDIA_TYPE_FILTER+':',
					this.srcTypeFilter,'->',this.searchField
				],
				frame: false,
		        loadMask:true,
				columnLines: false,
				scrollable:true,
				columns:columnConfig,
				bbar: Ext.create('Ext.PagingToolbar', {
		            store: this.dataStore,
		            displayInfo: true,
		            displayMsg: appLang.DISPLAYING_RECORDS+' {0} - {1} '+appLang.OF+' {2}',
		            emptyMsg:appLang.NO_RECORDS_TO_DISPLAY
		        })
			});

			this.dataPropertiesForm = Ext.create('Ext.form.Panel',{
					hidden:true,
					frame:true,
					border:false,
				scrollable:true,

					fieldDefaults:{
						labelAlign:'left',
						labelWidth:120,
						bodyStyle:'font-size:12px;',
						labelStyle: 'font-weight:bold;',
						xtype:'displayfield',
						anchor:"100%"
					},
					defaultType: 'displayfield',
					items: [
					    {
							xtype:'imagefield',
							fieldLabel:appLang.THUMBNAIL,
							name:'thumbnail'
						},{
							allowBlank: false,
							fieldLabel:appLang.TYPE,
							name:"type"
						},{
							allowBlank: false,
							fieldLabel:appLang.TITLE,
							name:"title"
						},{
							allowBlank: false,
							fieldLabel:appLang.SIZE_MB,
							name:"size"
						},{
							allowBlank: false,
							fieldLabel:appLang.UPLOADED_BY,
							name:"user_name"
						},{
							fieldLabel:appLang.ALTER_TEXT,
							name:"alttext"
						},{
							fieldLabel:appLang.CAPTION,
							name:"caption"
						},{
							fieldLabel:appLang.DESCRIPTION,
							name:"description"
						}]

			});

			this.dataProperties = Ext.create('Ext.Panel',{
				title:appLang.FILE_INFO,
				layout:'fit',
				frame:true,
				border:false,
				items:[this.dataPropertiesForm]
			});

			this.dataTree = Ext.create('app.medialib.CategoryTree',{
				title:appLang.MEDIA_CATEGORIES,
				layout:'fit',
				border:false,
				canEdit:this.canEdit,
				canDelete:this.canDelete,
				controllerUrl:app.createUrl([app.admin , 'mediacategory','']),
				listeners:{
					'itemSelected':{
						fn:function(id){
							this.selectedCategory = id;
							this.dataStore.proxy.setExtraParam('filter[category]', id);
							this.dataStore.load();
						},
						scope:this
					},
					'itemsPlaced':{
						fn:function(){
							this.dataStore.load();
						},
						scope:this
					}
				}
			});

			this.dataTabs = Ext.create('Ext.tab.Panel',{
				region:'east',
				deferredRender:false,
				layout:'fit',
				width:350,
				minWidth:350,
				scrollable:false,
				split:true,
				frame:true,
				border:false,
				items:[this.dataTree , this.dataProperties]
			});


			this.srcTypeFilter.on('select' , function(field, value, options){
				 	this.dataStore.proxy.setExtraParam('filter[type]' , field.getValue());
				 	this.dataStore.load();
			 },this);

			this.dataGrid.on('selectionchange',function(sm, selected){
				if(sm.hasSelection())
				{
					var record = sm.getLastSelected();
					this.dataPropertiesForm.getForm().reset();
					this.dataPropertiesForm.getForm().loadRecord(record);
					this.dataPropertiesForm.getForm().findField('thumbnail').setValue(record.get('thumbnail')+'?'+record.get('modified'));
					this.dataPropertiesForm.show();
				}
				else
				{
					this.dataPropertiesForm.hide();
				}
			},this);


			if(this.canEdit){
				this.dataGrid.on('itemdblclick',function(view , record , number , event , options){
					this.showEdit(record);
				},this);
			}

			if(this.canView){
				this.add([this.dataGrid , this.dataTabs]);
			}else{
				this.add([{xtype:'panel',layout:'fit',region:'center',html:'<center><h2>'+appLang.CANT_VIEW+'</h2></center>'}]);
			}

			this.fireEvent('createPanels');

		},
		showEdit:function(record){

			var win = Ext.create('app.medialib.EditWindow' , {
				mainGridId:this.dataGrid.getId(),
				viewFormId:this.dataPropertiesForm.getId(),
				recordId:record.get('id'),
				dataRec:record
			});

			win.on('dataSaved',function(){
				this.dataGrid.getSelectionModel().clearSelections();
				this.dataStore.load();
				win.close();
			},this);

			win.show();
		},
		deleteItem: function(record){
			var handler = this;
			Ext.Ajax.request({
					url: app.admin + app.delimiter + 'medialib' +  app.delimiter + 'remove',
		    		method: 'post',
		    		timeout:240000,
		    		params: {
		    			'id':record.get('id')
		    		},
		    		success: function(response, request) {
		    			response =  Ext.JSON.decode(response.responseText);
		    			if(response.success){
		    				handler.dataStore.remove(record);
		    			}else{
		    			    Ext.Msg.alert(appLang.MESSAGE, response.msg);
		    			}
		    	  },
		          failure:function() {
		          	Ext.Msg.alert(appLang.MESSAGE, appLang.CANT_EXEC);
		          }
		    	});
		}
});