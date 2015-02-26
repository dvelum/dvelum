Ext.ns('app');
app.content =  Ext.create('Ext.Panel',{
	region: 'center',
	frame:false,
	border:false,
	layout:'fit',
	margins: '0 5 0 0',
	autoScroll:false,
	items:[],
	collapsible:false
});
/**
 *
 * @event  projectLoaded
 */
Ext.application({
    name: 'DVelum Documentation',
    launch: function() {   
	app.application = this;
    	app.viewport = Ext.create('Ext.container.Viewport', {
    		layout: 'fit',
    		//renderTo: Ext.getBody(),
    		items:[app.content]
        });
    }
});