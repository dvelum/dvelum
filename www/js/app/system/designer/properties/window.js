/**
 * Properties panel for Window object
 */
Ext.define('designer.properties.Window',{
	extend:'designer.properties.Panel',
	
	initComponent:function()
	{
		if(!this.tbar){
			this.tbar = [];
		}
		
		this.tbar.push({
        	 icon:app.wwwRoot + 'i/system/designer/window-open.png',
        	 text:desLang.showWindow,
        	 scope:this,
        	 handler:this.showWindow
		});
		
		this.callParent();	
	},

	showWindow:function(){
		app.designer.switchView(0);
		app.designer.sendCommand({command:'showWindow',params:{name:this.objectName}});
	}
});