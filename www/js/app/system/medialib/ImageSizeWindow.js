Ext.ns('app.medialib');

/**
 * Media library item edit window
 * @extend {Ext.Window}
 *
 *
 * @event sizeSelected
 * @param string size
 *
 * @event selectCanceled
 *
 */
Ext.define('app.imageSizeWindow',{
	
	extend:'Ext.Window',
	constructor: function(config) {
		config = Ext.apply({
			modal: true,
	        layout:'fit',
	        title: appLang.MODULE_MEDIALIB + ' :: '+ appLang.SELECT_IMAGE_SIZE,
	        width: 300,
	        height: 198,
			scrollable:true,
	        closeAction: 'destroy',
	        resizable:true,
	        bodyPadding:3
	    }, config || {});		
		
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
	  }
});