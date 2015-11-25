Ext.define('app.medialib.HtmlPanel',{
	extend:'Ext.panel.Panel',
	alias:'widget.medialibhtmlpanel',
	editorName:'text',
	frame:false,
	textEditor:null,
	value:'',
	
	constructor: function(config) {	
		config.layout = 'fit';
		config = Ext.apply({
			bodyPadding:0,
			border:false,
			frame:false,
			tbar:[]
	    }, config || {});		
		config.fieldLabel='';
		config.fieldDefaults = {};
		this.callParent(arguments);
	},
	initComponent:function(){
		
		
		this.textEditor = Ext.create('Ext.ux.CKeditor',{
			CKConfig:{
			    skin : 'moono',
			    uiColor:'#EDEDED',
			    //enterMode:CKEDITOR.ENTER_BR,
			    toolbar : 
			    [
        	               { name: 'document',    items : [ 'Source','-','DocProps','Preview','Print','-','Templates' ] },
        	               { name: 'clipboard',   items : [ 'Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo' ] },
        	               { name: 'editing',     items : [ 'Find','Replace','-','SelectAll','-','SpellChecker', 'Scayt' ] },
        	               '/',
        	               { name: 'basicstyles', items : [ 'Bold','Italic','Underline','Strike','Subscript','Superscript','-','RemoveFormat' ] },
        	               { name: 'paragraph',   items : [ 'NumberedList','BulletedList','-','Outdent','Indent','-','Blockquote','CreateDiv','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock','-','BidiLtr','BidiRtl' ] },
        	               { name: 'links',       items : [ 'Link','Unlink','Anchor' ] },
        	               { name: 'insert',      items : [ 'Image','Flash','Table','HorizontalRule','Smiley','SpecialChar','PageBreak' ] },
        	               '/',
        	               { name: 'styles',      items : [ 'Styles','Format','Font','FontSize' ] },
        	               { name: 'colors',      items : [ 'TextColor','BGColor' ] },
        	               { name: 'tools',       items : [ 'Maximize', 'ShowBlocks','-','About' ] }
			    ],
			    baseFloatZIndex:100000,
			    contentsCss:[
			      app.wwwRoot+'css/public/main/style.css',
			      app.wwwRoot+'css/public/main/editor.css'
			    ],
			    bodyClass:'content',
			    bodyId:'content',
			    resize_enabled:false,
			    allowedContent:true
			},
			name:this.editorName
		});
		
		var me = this;
		this.tbar = [{
	    	  tooltip:appLang.MSG_INSERT_MEDIA_IMAGE,
	    	  iconCls:'addImageIcon',
	    	   listeners:{
	    		  click:{
	    			  fn:function(){
	    				  
	    				  var win = Ext.create('app.selectMediaItemWindow',{
	    	    			  actionType:'selectId',
	    	    			  resourceType:'image'
	    	    		  });
	    				  
	    				  
	    				  win.on('itemSelected',function(records){
    		    	    			  var selSizeWin = Ext.create('app.imageSizeWindow',{
    		    	    				  	listeners:{
    		    	    				  		sizeSelected:{
    		    	    				  			fn:function(size){
    		    	    				  				Ext.each(records,function(record){
    		    	    				  				var path = record.get('path');
      			    		    	    				  var ext = record.get('ext');
      			    		    	    				  path = path.replace(ext , (size+ext));
      			    		    	    				  me.textEditor.getEditor().insertHtml('<img src="'+path+'" alt="'+record.get('alttext')+'" title="'+record.get('title')+'"> ');
    		    	    				  				});
    		    	    				  				win.close();
    		    	    				  			},
    		    	    				  			scope:this
    		    	    				  		}
    		    	    				  	}
    		    	    			  }).show(); 

    	    			  },this);
	    				  win.show();
	    			  },
	    			  scope:this
	    		  }
	    	  }
	      }, {
	    	  tooltip:appLang.MSG_INSERT_MEDIA_RESOURCE,
	    	  iconCls:'fileIcon',
	    	   listeners:{
	    		  click:{
	    			  fn:function(){
	    				  var win = Ext.create('app.selectMediaItemWindow',{
	    	    			  actionType:'selectId',
	    	    			  resourceType:'all'
	    	    		  });  
	    	    		  win.on('itemSelected' , function(records){	
	    	    			  Ext.each(records , function(record){
	    	    				  me.textEditor.getEditor().insertHtml('<a href="'+record.get('path')+'">'+record.get('title')+'</a> ');
	    	    			  });
	    	    			  win.close();
	    	    		  },this);
	    	    		  win.show();    	    		  
	    			  },
	    			  scope:this
	    		  }
	    	  }
	     }];
	     
		this.callParent();		
		this.add(this.textEditor);
	},
	
	getTextEditor:function(){
			return this.textEditor;
	},
	
	setValue:function(value){
	    this.textEditor.setValue(value);
	},
	getValue:function(){
	    return this.textEditor.getValue();
	}
});