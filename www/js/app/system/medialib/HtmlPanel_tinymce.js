Ext.define('app.medialib.HtmlPanel',{
	extend:'Ext.panel.Panel',
	alias:'widget.medialibhtmlpanel',
	editorName:'text',
	frame:false,
	textEditor:null,
	value:'',
	layout:'fit',
	bodyPadding:0,
	padding:0,
	margin:0,
	border:false,
	frame:false,
	
	constructor: function(config) {	
		config.layout = 'fit';
		config = Ext.apply({
			tbar:[]
	    }, config || {});		
		config.fieldLabel='';
		config.fieldDefaults = {};
		this.callParent(arguments);
	},
	initComponent:function(){
		
		this.textEditor = Ext.create('Ext.ux.TinyMCE',{
			padding:0,
			name:this.editorName,
			tinymceSettings:{	
					theme : "advanced",
					plugins: "pagebreak,fullscreen,style,layer,table,advhr,advimage,advlink,iespell,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,noneditable,visualchars,nonbreaking,xhtmlxtras",
					theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect",
					theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
					theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|",
					theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak",
					theme_advanced_toolbar_location : "top",
					theme_advanced_toolbar_align : "left",
					theme_advanced_statusbar_location : "bottom",
					theme_advanced_resizing : false,
					extended_valid_elements : "div[class|id|style|align],a[class|id|name|href|target|title|onclick],img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name|style|class|id|],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style],iframe[width|height|src|frameborder|allowfullscreen]",
					//template_external_list_url : "template_list.js",
					accessibility_focus : false,
					skin:'o2k7',
					skin_variant : "silver",
					convert_urls : false,
					//force_br_newlines : true,
					forced_root_block : false,
			        force_p_newlines : true,
			        entity_encoding : "raw",
			        content_css : app.wwwRoot+"css/public/main/style.css,"+app.wwwRoot+"css/public/main/editor.css,"
			}
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
	    				  
	    				  
	    				  win.on('itemSelected',function(record){
    		    	    			  var selSizeWin = Ext.create('app.imageSizeWindow',{
    		    	    				  	imagerecord:record,
    		    	    				  	listeners:{
    		    	    				  		sizeSelected:{
    		    	    				  			fn:function(size){
    		    	    				  				Ext.each(records,function(record){
    		    	    				  				var path = record.get('path');
      			    		    	    				  var ext = record.get('ext');
      			    		    	    				  path = path.replace(ext , (size+ext));
      			    		    	    				  me.textEditor.getEd().execCommand('mceInsertRawHTML',true,'<img src="'+path+'" alt="'+record.get('alttext')+'" title="'+record.get('title')+'"> ');      			    		    	    			
    		    	    				  				},this);
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
	    	    				  this.textEditor.getEd().execCommand('mceInsertRawHTML',true,'<a href="'+record.get('path')+'">'+record.get('title')+'</a> ');
	    	    			  }),this;
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