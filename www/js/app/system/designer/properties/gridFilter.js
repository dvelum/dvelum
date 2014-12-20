/**
 * Properties panel for Grid Filter object
 */
Ext.define('designer.properties.GridFilter',{
	extend:'designer.properties.Panel',

	autoLoadData:false,
	
	initComponent:function(){
	    
	    this.sourceConfig = Ext.apply({		
		'options':{
		    editor:Ext.create('Ext.form.field.Text',{
			listeners:{
			    focus:{
				fn:this.showOptionsWindow,
				scope:this
			    }
			}
		    }),
		    renderer:function(v){return '...';}
		}
	    } , this.sourceConfig );	    
	    this.callParent();
	},
	showOptionsWindow:function(){
	    var source = this.dataGrid.getSource();
	    var result = [];
	    var data = [];
	    
	    if(source.options.length){
		data = Ext.JSON.decode(source.options);
	    }
	    
	    if(!Ext.isEmpty(data)){
		Ext.each(data,function(record , index){
		   result.push({value:record}); 
		});
	    }
	    
	    var win = Ext.create('designer.grid.filterOptionsWindow',{
		objectName:this.objectName,
		controllerUrl:this.controllerUrl,
		initialData:result
	    });
	    win.on('dataChanged',this.dataGrid.setProperty,this.dataGrid);
	    win.show();
	}
});