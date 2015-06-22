Ext.ns('app');
app.content =  Ext.create('Ext.Panel',{
	region: 'center',
	frame:false,
	border:false,
	layout:'fit',
	margins: '0 5 0 0',
	scrollable:false,
	items:[],
	collapsible:false
});
		
Ext.application({
    name: 'DVelum Documentation',
    launch: function() {   
	app.application = this;
	this.addEvents('projectLoaded');
    	app.viewport = Ext.create('Ext.container.Viewport', {
    		layout: 'fit',
    		//renderTo: Ext.getBody(),
    		items:[app.content]
        });
    }
});