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
 * Media library item edit window
 * @extend {Ext.Window}
 */
Ext.define('app.imageSizeWindow',{

	extend:'Ext.Window',

	imageRecord:null,

	constructor: function(config) {
		config = Ext.apply({
			modal: true,
	        layout:'fit',
	        title: appLang.MODULE_MEDIALIB + ' :: '+ appLang.SELECT_IMAGE_SIZE,
	        width: 300,
	        height: 198,
	        autoScroll:true,
	        closeAction: 'destroy',
	        resizable:true,
	        bodyPadding:3
	    }, config);

		this.callParent(arguments);
	},
	initComponent : function()
	{
			var original = {
				name:'size' ,
				boxLabel:appLang.ORIGINAL ,
				value:'',
				inputValue:'',
				checked:true
			};

			var groupItems = [original];


			for(index in  app.imageSize)
			{
				if(typeof app.imageSize[index] == 'function' || app.imageSize[index] == undefined){
					continue;
				}
				groupItems.push({
					name:'size' ,
					boxLabel:index +' ('+app.imageSize[index][0]+'x'+app.imageSize[index][1]+')',
					inputValue:'-' + index
				});
			}


			this.height = groupItems.length * 30 + 40;

			this.groupFld = Ext.create('Ext.form.RadioGroup',{
				xtype:'radiogroup',
				columns:1,
				width:250,
				vertical: true,
				items:groupItems
			});

			this.items = [this.groupFld];

			this.buttons=[
			     {
			    	 text:appLang.SELECT,
			    	 listeners:{
			    		 click:{
			    			 fn:function(){
			    				 var value = this.groupFld.getValue().size;
			    				 this.fireEvent('sizeSelected', value);
			    				 this.close();
			    			 },
			    			 scope:this
			    		 }
			    	 }
			     },{
			    	 text:appLang.CLOSE,
			    	 listeners:{
			    		 click:{
			    			 fn:function(){
			    				 this.fireEvent('selectCanceled');
			    				 this.close();
			    			 },
			    			 scope:this
			    		 }
			    	 }
			     }
			 ];


		 	this.callParent();
	        this.addEvents(
	            /**
	             * @event sizeSelected
	             * @param string size
	             */
	           'sizeSelected',
	           /**
	            * @event selectCanceled
	            */
	           'selectCanceled'
	       );

	  }
});



Ext.define('app.medialibFilesModel', {
    extend: 'Ext.data.Model',
    fields: [
              {name:'id',type:'integer'},
	          {name:'type',type:'string'},
	          {name:'url',type:'string'},
	          {name:'thumb',type:'string'},
	          {name:'thumbnail',type:'string'},
	          {name:'title'},
	          {name:'size'},
     	      {name:'srcpath', type:'string'},
     	      {name:'ext',type:'string'},
     	      {name:'path',type:'string'},
     	      {name:'icon', type:'string'}
    ]
});
/**
 * Media library file upload  Window
 * @author Kirill Egorov 2011
 * @extend Ext.Window
 */
Ext.define('app.fileUploadWindow',{
	extend:'Ext.Window',

	contentPanel:null,

	constructor: function(config) {
			config = Ext.apply({
				cls:'upload_window',
				modal: true,
		        layout:'fit',
		        title: appLang.MODULE_MEDIALIB + ' :: '+ appLang.FILE_UPLOAD,
		        width: 420,
		        height: 500,
		        closeAction: 'destroy',
		        resizable:false,
		        items:[]
		    }, config);

		this.callParent(arguments);
	},

	initComponent:function(){
		var me = this;
		var accExtString = '<div><b>'+appLang.MAX_UPLOAD_FILE_SIZE+'</b><br> ' + app.maxFileSize + '<br>';

		for(i in app.mediaConfig) {
			if(Ext.isEmpty(i.title)){
				accExtString+='<b>' + app.mediaConfig[i].title + '</b><br> ';
				var cnt = 0;
				var len = app.mediaConfig[i].extensions.length;
				Ext.each(app.mediaConfig[i].extensions , function(extName){
					if(cnt < (len-1)){
						accExtString+=extName+', ';
					}else{
						accExtString+=extName;
					}
					cnt++;
				});
				accExtString+='<br>';
			}
		}
		accExtString+='</div>';

		this.simpleUpload = Ext.create('Ext.form.Panel',{
			  region:'north',
			  fileUpload:true,
			  padding:5,
			  height:80,
			  frame:true,
			  border:false,
			  layout:'hbox',
			  fieldDefaults:{
				  anchor:"100%",
				  hideLabel:true
			  },
			  items:[{
			  	    	     xtype: 'filefield',
			  	    	     emptyText: appLang.SELECT_FILE,
			  	    	     flex:1,
				  		     buttonText: '',
				             buttonConfig: {
				                 iconCls: 'upload-icon'
				             },
			  	    	   	 name:'file'
			  	       },{
			  	    	  xtype:'label',
			 			  text:appLang.ACCEPTED_FORMATS,
			 			  style:{
			 	        		textDecoration:'underline',
			 	        		padding:'5px',
			 	        		fontSize:'10px',
			 	        		color:'#3F1BF6',
			 	        		cursor:'pointer'
			 	          },
			 	          listeners:{
			 	        	  afterrender:{
			 	        		  fn:function(cmp){
			 	        			  cmp.getEl().on('click',function(){
			 	        				  Ext.Msg.alert(appLang.ACCEPTED_FORMATS, accExtString);
			 	        			  });
			 	        		  },
			 	        		  scope:this
			 	        	  }
			 	          }
			 		  }
			  	],
			  	buttons:[
					{
						text:appLang.UPLOAD,
						listeners:{
							'click':{
								fn:function(){
									 this.simpleUploadStart();
								},
								scope:this
							}
						}
					}
			  	]
		  });

		  this.simpleUploadedGrid =  Ext.create('Ext.grid.Panel',{
			  region:'center',
			  store: Ext.create('Ext.data.Store',{
			      autoLoad:false,
				  idProperty:'url',
				  model:'app.medialibFilesModel'
			  }),
			  viewConfig:{
				  stripeRows:true
			  },
			  frame: false,
		      loadMask:true,
			  columnLines: false,
			  autoScroll:true,
			  columns:[
			           {
			        	  text:appLang.ICON,
			        	  dataIndex:'thumb',
			        	  align:'center',
			        	  xtype:'templatecolumn',
			        	  tpl:new Ext.XTemplate(
	        			   		'<div style="white-space:normal;">',
	        			   			'<img src="{thumb}" alt="[icon]" style="border:1px solid #000000;" height="32"/>',
	        			    	'</div>'
			        	   ),
			        	   width:80
			           }, {
			        	   text:appLang.INFO,
			        	   dataIndex:'id',
			        	   flex:1,
			        	   xtype:'templatecolumn',
			        	   tpl: new Ext.XTemplate(
			        			   '<div style="white-space:normal;">',
			        			     + appLang.TYPE + ': {type}<br>',
			        			     + appLang.TITLE + ': {title}<br>',
			        			     + appLang.SIZE + ': {size}<br>',
			        			    '</div>'
			        	   )
			           },
			           {
			        	   xtype:'actioncolumn',
			        	   width:40,
			        	   items:[
			        	          {
			        	        	  icon:'/i/system/edit.png',
			        	        	  tooltip:appLang.EDIT_RESOURCE,
			        	        	  scope:this,
			        	        	  handler:function(grid, rowIndex, colIndex){
			        	        		   var record = grid.getStore().getAt(rowIndex);
			        	        		   var win = Ext.create('app.medialib.EditWindow',{
			        	        			  'recordId':record.get('id'),
			        	        			  'dataRec':record
			        	        		   });

			        	        			win.on('dataSaved',function(){
			        	        				grid.getStore().removeAt(rowIndex);
			        	        				this.fireEvent('filesuploaded');
			        	    					win.close();
			        	    				},this);
			        	        		   win.show();
			        	        	  }
			        	          }
			        	   ]
			           }
			   ]
		  });

		  this.multipleUploadedGrid = Ext.create('Ext.grid.Panel',{
			  region:'center',
			  store: Ext.create('Ext.data.Store',{
			      autoLoad:false,
				  idProperty:'id',
				  fields: [
				             {name:'id',type:'integer'},
				             {name:'icon',type:'string'},
				             {name:'progress',type:'float'},
				             {name:'name',type:'string'},
				             {name:'uploaded' , type:'boolean'},
				             {name:'uploadError' , type:'string'}

				  ]
			  }),
			  viewConfig:{
				  stripeRows:true
			  },
			  frame: false,
		      loadMask:true,
			  columnLines: false,
			  autoScroll:true,
			  columns:[
			           {
			        	  text:appLang.ICON,
			        	  dataIndex:'icon',
			        	  align:'center',
			        	  xtype:'templatecolumn',
			        	  tpl:new Ext.XTemplate(
	        			   		'<div style="white-space:normal;">',
	        			   			'<img src="{icon}" alt="[icon]" style="border:1px solid #000000;" height="32"/>',
	        			    	'</div>'
			        	   ),
			        	   width:80
			           },{
			        	   text:appLang.NAME,
			        	   dataIndex:'name',
			        	   flex:1,
			        	   renderer:function(v , m ,r){
			        		   if(r.get('uploadError').length){
			        			   v+='<br><span style="color:red;">'+r.get('uploadError')+'</span>';
			        		   }
			        		   return v;
			        	   }
			           },{
			        	   text:appLang.PROGRESS,
			        	   dataIndex:'progress',
			        	   width:100,
			        	   renderer:app.progressRenderer
			           },{
			        	   width:40,
				           dataIndex:'id',
				           renderer:function(v,m,r){
				           if(r.get('uploaded')){
				        			  return '<img src="/i/system/edit.png" title="'+appLang.EDIT_RESOURCE+'">';
				        	  }else{
				        		  return '';
				        	  }
				           }
				       }
			   ]
		  });


		  this.multipleUploadedGrid.on('itemclick',function( grid, record, item, index, e, eOpts) {
		        if(record.get('uploaded')){
		        	var file = this.ajaxUploadField.getFile(record.get('id'));

		        	if(!file || !file.uploadResult){
		        		return;
		        	}

		        	var item = file.uploadResult.data[0];

		        	var win = Ext.create('app.medialib.EditWindow',{
	        			  'recordId':item.id,
	        			  'dataRec': new app.medialibFilesModel(item)
	        		});

		        	win.on('dataSaved',function(){
        				this.fireEvent('filesuploaded');
    					win.close();
    				},this);

	        		win.show();
		        }
		  },this);


		  this.ajaxUploadField = Ext.create('Ext.ux.form.AjaxFileUploadField',{
			  emptyText: appLang.SELECT_FILE,
	  		  buttonText: appLang.MULTIPLE_FILE_UPLOAD,
	  		  buttonOnly:true,
	  		  defaultIcon:'/i/unknown.png',
	  		  url: app.createUrl([app.admin ,'medialib' , 'upload']),
	          buttonConfig: {
	                 iconCls: 'upload-icon'
	          },
	          listeners:{
	        	  'filesSelected':{
	        		  fn:me.onMFilesSelected,
	        		  scope:this
	        	  },
	        	  'fileUploaded':{
	        		  fn:me.onMFileUploaded,
	        		  scope:this
	        	  },
	        	  'fileUploadProgress':{
	        		  fn:me.onMFilesUploadProgress,
	        		  scope:this
	        	  },
	        	  'fileUploadError':{
	        		  fn:me.onMFilesUploadError,
	        		  scope:this
	        	  },
	        	  'fileImageLoaded':{
	        		  fn:me.onMFilesImageLoaded,
	        		  scope:this
	        	  },
	        	  'filesUploaded':{
	        		  fn:me.onMFilesUploaded,
	        		  scope:this
	        	  }
	          }
		  });

		  var linkLabel = Ext.create('Ext.form.Label',{
			  text:appLang.ACCEPTED_FORMATS,
			  style:{
				  textDecoration:'underline',
				  padding:'5px',
				  fontSize:'10px',
				  color:'#3F1BF6',
				  cursor:'pointer'
			  },
			  listeners:{
				  afterrender:{
					  fn:function(cmp){
						  cmp.getEl().on('click',function(){
							Ext.Msg.alert(appLang.ACCEPTED_FORMATS,accExtString);
						  },me);
					  },
					  scope:this
				  }
			  }
		  });

		  this.mClearButton = Ext.create('Ext.Button' , {
			  text:appLang.CLEAR,
			  disabled:true,
			  listeners:{
					'click':{
						fn:function(){
							this.ajaxUploadField.reset();
							this.multipleUploadedGrid.getStore().removeAll();
						},
						scope:this
					}
			  }
		  });
		  this.mUploadButton = Ext.create('Ext.Button' , {
			  text:appLang.UPLOAD,
			  disabled:true,
			  listeners:{
					'click':{
						fn:function(){
							this.ajaxUploadField.upload();
						},
						scope:this
					}
			  }
		  });

		  this.multipleUpload = Ext.create('Ext.Panel',{
			  region:'north',
			  fileUpload:true,
			  padding:5,
			  height:80,
			  frame:true,
			  border:false,
			  fieldDefaults:{
				  anchor:"100%",
				  hideLabel:true
			  },
			  layout:'hbox',
			  items:[
    	        this.ajaxUploadField,
    	        {
    	        	xtype:'label',
    	        	flex:1
    	        },
    	        linkLabel
		     ],
			 buttons:[this.mClearButton, this.mUploadButton]
		  });

		this.simplePanel = Ext.create('Ext.Panel',{
			  title:appLang.SIMPLE_UPLOAD,
			  layout:'border',
			  items:[
			         this.simpleUpload,  this.simpleUploadedGrid
			  ]
		});

		this.multiplePanel = Ext.create('Ext.Panel',{
			  title:appLang.MULTIPLE_FILE_UPLOAD,
			  layout:'border',
			  items:[
			         this.multipleUpload,  this.multipleUploadedGrid
			  ]
		});

		this.contentPanel = Ext.create('Ext.tab.Panel' , {
			  activeTab: 1,
			  frame:true,
			  autoScroll:true,
			  items:[this.simplePanel , this.multiplePanel]
		});

		this.items=[this.contentPanel];

		this.callParent();

		this.addEvents('filesuploaded');
	},
	onMFilesImageLoaded:function(index , icon){
		var store = this.multipleUploadedGrid.getStore();
		var rIndex = store.findExact('id',index);
		if(index!=-1)
		{
			var rec = store.getAt(rIndex);
			rec.set('icon' , icon);
			rec.commit();
		}
	},
	onMFilesSelected:function(files){
		var me = this;
		var data = [];
		if(this.ajaxUploadField.filesCount()){
			Ext.each(this.ajaxUploadField.getFiles() , function(file , index){
				var progress;
			    file.uploaded?progress = 100:progress = 0;
				data.push({
					id:index,
					name:file.name,
					icon:file.icon,
					progress:progress,
					uploaded:file.uploaded,
					uploadError:file.uploadError
				});
			},me);
		}
		this.multipleUploadedGrid.getStore().loadData(data);

		if(!Ext.isEmpty(data)){
			this.mClearButton.enable();
			this.mUploadButton.enable();
		}else{
			this.mClearButton.disable();
			this.mUploadButton.disable();
		}
	},

	onMFileUploaded:function(index , result){
		var store = this.multipleUploadedGrid.getStore();
		var rIndex = store.findExact('id',index);
		if(index!=-1){
			var rec = store.getAt(rIndex);
			rec.set('uploaded' , 1);
			rec.commit();
		}
	},

	onMFilesUploadProgress:function(index , uploaded , total){
		var store = this.multipleUploadedGrid.getStore();
		var rIndex = store.findExact('id',index);
		if(index!=-1){
			var rec = store.getAt(rIndex);
			rec.set('progress' , (uploaded *100) / total);
			rec.commit();
		}
	},

	onMFilesUploadError:function(index , result){
		var file = this.ajaxUploadField.getFile(index);
		if(!file){
			return;
		}
		var store = this.multipleUploadedGrid.getStore();
		var rIndex = store.findExact('id',index);
		if(index!=-1){
			var rec = store.getAt(rIndex);
			rec.set('uploadError' , file.uploadError);
			rec.set('progress' ,99);
			rec.commit();
		}
 	},
 	onMFilesUploaded:function(){
 		this.fireEvent('filesuploaded');
 	},
	/**
	 * Simple upload form submit
	 */
	simpleUploadStart:function(){
	    var handle = this;
		this.simpleUpload.getForm().submit({
			    clientValidation: true,
        		url:app.createUrl([app.admin ,'medialib' , 'upload']),
                waitMsg: appLang.UPLOADING,
                success: function(form, responce)
                {
                	var dat = responce.result.data ;
                	 Ext.each(dat,function(item){
                		 var rec = new app.medialibFilesModel({
	                             'id':item.id,
	                             'type':item.type,
	                             'url': item.url,
	                             'thumb': item.thumb,
	                             'thumbnail':item.thumbnail,
	                             'name':false,
	                             'title':item.title,
	                             'size':item.size,
	                             'srcpath':item.srcpath,
	                             'icon':item.icon,
	                             'ext':item.ext,
	                             'path':item.path
                         });
                		 handle.simpleUploadedGrid.getStore().insert(0, rec);
                	 });
                	 handle.simpleUpload.getForm().reset();
                	 handle.fireEvent('filesuploaded');
                },
            	failure: app.formFailure
            });
         }
});

Ext.define('app.medialibModel', {
    extend: 'Ext.data.Model',
    fields: [
             {name:'id' , type:'integer'},
    	     {name:'thumb' , type:'string'},
       	     {name:'date', type:"date", dateFormat: "Y-m-d H:i:s"},
       	     {name:'modified',type:'string'},
       	     {name:'title',type:'string'},
       	     {name:'alttext',type:'string'},
     	     {name:'text',type:'string'},
     	     {name:'caption',type:'string'},
     	     {name:'description',type:'string'},
     	     {name:'size',type:'float'},
     	     {name:'user_id',type:'integer'},
     	     {name:'path',type:'string'},
     	     {name:'type',type:'string'},
     	     {name:'user_name',type:'string'},
     	     {name:'ext',type:'string'},
     	     {name:'srcpath', type:'string'},
     	     {name:'thumbnail', type:'string'},
     	     {name:'icon', type:'string'}
    ]
});

/**
 * Media library panel component
 * @author Kirill Egorov 2011
 * @extend Ext.Panel
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

	   searchField:null,

	   srcTypeFilter:null,

	   checkRights:false,
	   canEdit:false,
	   canDelete:false,
	   canView:true,

	   constructor: function(config) {
			config = Ext.apply({
				layout:'border',
				tbar: new Ext.Panel({
					border:false,
					bodyBorder:false,
					items:[]
				})
		    }, config);
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
		   this.addEvents('rightsChecked');

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
	   createPanels:function()
	   {
			this.dataStore = Ext.create('Ext.data.Store', {
			    model: 'app.medialibModel',
			    proxy: {
			        type: 'ajax',
			        url: app.admin + app.delimiter + 'medialib' +  app.delimiter + 'list',
			        reader: {
			            type: 'json',
			            root: 'data',
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
			    autoLoad: true,
			    sorters: [{
	                  property : 'date',
	                  direction: 'DESC'
	            }]
			});

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
			            	    	   icon:'/i/system/edit.png',
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
						    	   icon:'/i/system/delete.gif',
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

			this.dataGrid = Ext.create('Ext.grid.Panel',{
				store:this.dataStore,
				region:'center',
				viewConfig:{
					stripeRows:true
				},
				tbar:[
							  {
									text:appLang.ADD_FILES,
									hidden:!this.canEdit,
									listeners:{
										click:{
												fn:function(){
									  					var win =  Ext.create('app.fileUploadWindow',{});
									  					win.on('filesuploaded',function(){
									  						this.dataStore.load();
									  					},this);
									  					win.show();
												},
												scope:this
										}
									}
								},'-',
					       		appLang.MEDIA_TYPE_FILTER+':',
					       		this.srcTypeFilter,'->',this.searchField
				],
				frame: false,
		        loadMask:true,
				columnLines: false,
				autoScroll:true,
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
					autoScroll:true,

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
					region:'east',
					layout:'fit',
					width:350,
					minWidth:350,
					autoScroll:false,
					split:true,
					frame:true,
					border:false,
					items:[this.dataPropertiesForm]
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
				this.add([this.dataGrid , this.dataProperties]);
			}else{
				this.add([{xtype:'panel',layout:'fit',region:'center',html:'<center><h2>'+appLang.CANT_VIEW+'</h2></center>'}]);
			}


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

Ext.define('app.selectMediaItemWindow',{
	extend:'Ext.Window',
	/**
	 * @var {app.medialibPanel }
	 */
	medialibPanel:null,
	actionType:'selectId',
	resourceType:'all',

	constructor: function(config) {
		config = Ext.apply({
			modal: true,
	        layout:'fit',
	        title: appLang.MODULE_MEDIALIB + ' :: '+ appLang.EDIT_ITEM,
	        width: 750,
	        height: app.checkHeight(600),
	        closeAction: 'destroy',
	        resizable:true,
	        items:[],
	        maximizable:true
	    }, config);
		this.callParent(arguments);
	},
	selectItem:function()
	{

		switch(this.actionType){
			case 'selectId' :
				var sm = this.medialibPanel.dataGrid.getSelectionModel();

				if(!sm.hasSelection()){
					Ext.MessageBox.alert(appLang.MESSAGE,appLang.MSG_SELECT_RESOURCE);
					return;
				}

				var rec = sm.getSelection()[0];

				if(this.resourceType!='all' && this.resourceType != rec.get('type')){
					Ext.MessageBox.alert(appLang.MESSAGE,appLang.SELECT_PLEASE+' '+this.resourceType + ' '+ appLang.RESOURCE);
					return;
				}

				this.fireEvent('itemSelected', rec);
				break;
		}
	},
    initComponent : function(){

		this.medialibPanel = Ext.create('app.medialibPanel',{
			 actionType:this.actionType,
			 border:false,
			 checkRights:true
		});

		this.items=[this.medialibPanel];

		if(this.resourceType !='all'){
			this.medialibPanel.on('rightsChecked',function(){
				this.medialibPanel.srcTypeFilter.setValue(this.resourceType);
				this.medialibPanel.srcTypeFilter.disable();
				this.medialibPanel.dataStore.proxy.setExtraParam('filter[type]' , this.resourceType);
				this.medialibPanel.dataStore.load();
			},this);
		}

		var me = this;

		this.buttons=[
		           {
		        	    text:appLang.SELECT,
		        	    scope:me,
		        	    handler:me.selectItem
		           },{
		        	   text:appLang.CLOSE,
		        	   scope:me,
		        	   handler:me.close
		           }
		];

		this.callParent(arguments);
        this.addEvents(
            /**
             * @event itemSelected
             * @param {Ext.data.Record} record
             */
            'itemSelected'
        );
	}
});
