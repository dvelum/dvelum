Ext.ns('app.medialib');

/**
 * Media library file upload  Window
 * @author Kirill Egorov 2011
 * @extend Ext.Window
 *
 * @event filesuploaded
 *
 */
Ext.define('app.fileUploadWindow',{
	extend:'Ext.Window', 
	
	contentPanel:null,
	
	uploadUrl:null,
	
	selectedCategory:0,

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
		    }, config || {});		
			
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
			  scrollable:true,
			  columns:[
			           {
			        	  text:appLang.ICON,
			        	  dataIndex:'thumb',
			        	  align:'center',
			        	  xtype:'templatecolumn',
			        	  tpl:new Ext.XTemplate(
	        			   		'<div style="white-space:normal;">',
	        			   			'<img src="{icon}" alt="[icon]" style="border:1px solid #000000;" height="32"/>',
	        			    	'</div>'
			        	   ),
			        	   width:80
			           }, {
			        	   text:appLang.INFO,
			        	   dataIndex:'id',
			        	   flex:1,
			        	   xtype:'templatecolumn',
			        	   tpl: function(){return new Ext.XTemplate(
			        			   '<div style="white-space:normal;">',
			        			    ' ' + appLang.TYPE + ': {type}<br>',
			        			    ' ' + appLang.TITLE + ': {title}<br>',
			        			    ' ' + appLang.SIZE + ': {size}<br>',
			        			    '</div>'
			        	   );}()
			           },
			           {
			        	   xtype:'actioncolumn',
			        	   width:40,
			        	   items:[
			        	          {
			        	        	  iconCls:'editIcon',
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
			  scrollable:true,
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
				        			  return '<img src="'+app.wwwRoot+'i/system/edit.png" title="'+appLang.EDIT_RESOURCE+'">';
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
	  		  defaultIcon: app.wwwRoot + 'i/unknown.png',
	  		  url: this.uploadUrl,
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
			  scrollable:true,
			  items:[this.simplePanel , this.multiplePanel]
		});
		
		this.items=[this.contentPanel];
	
		this.callParent();
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
			    url: this.uploadUrl,
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