/**
 *
 * @event dataSelected
 * @param {Ext.data.record}
 *
 */
Ext.define('Ext.form.medialinkField', {
	extend:'Ext.Panel', 
	alias:'widget.medialinkfield',
	imgCfg:null,
	linkName:null,
	imgTitleCfg:null,
	bodyCls:'formBody',
	padding:{left:155,top:5,bottom:5},
	resourceType:'image',

	img:null,
	link:null,
	ttl:null,
	
	constructor: function(config) {
		
		config = Ext.apply({
			border:false,
			frame:false,
	        items:[]
	    }, config || {});	
	
		this.callParent(arguments);
	    
	    this.img = Ext.create('app.ImageField',config.imgCfg);	   
		this.link = new Ext.form.field.Hidden({name:config.linkName});
		this.ttl = new Ext.form.field.Display(config.imgTitleCfg);

	   
	       this.add([
	       {
	     	  xtype:'button',
	    	  iconCls:'editIcon2',
	    	  text:appLang.SELECT_RESOURCE,
	    	  width:30,
	    	  height:20,
	    	  listeners:{
	    		  'click':{
	    			  fn:function(){
	    				  var win = Ext.create('app.selectMediaItemWindow',{
	    	    			  actionType:'selectId',
	    	    			  resourceType:this.resourceType
	    	    		  });
	    	    		  
	    	    		  win.on('itemSelected' , function(record){
	    	    			 
	    	    			  if(Ext.isArray(record)){
	    	    				  record = record[0];
	    	    			  }
	    	    			  this.link.setValue(record.get('id'));
	    	    			  this.img.setValue(record.get('thumbnail'));
	    	    			  this.ttl.setValue( record.get('title'));
	    	    			  this.fireEvent('dataSelected' , record);
	    	    			  win.close();
	    	    		  },this);
	    	    		  win.show();
	    			  },
	    			  scope:this
	    		  }
	    	  }
	      },
	      this.ttl ,
	      this.img ,
	      this.link
	      ]);
	   
	},
	initComponent : function(){
		  this.callParent(arguments);
  }
	
});

