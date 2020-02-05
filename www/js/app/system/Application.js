app.application = false;
app.content =  Ext.create('Ext.Panel',{
	frame:false,
	border:false,
	bodyBorder:false,
	layout:'fit',
	//margins: '0 5 0 0',
	scrollable:false,
	items:[],
	collapsible:false,
	flex : 1
});
app.header = Ext.create('Ext.Panel',{
	contentEl:'header',
	bodyCls:'formBody',
	cls: 'adminHeader',
	height: 30
});

app.cookieProvider = new Ext.state.CookieProvider({
	expires: new Date(new Date().getTime()+(1000*60*60*24)) //1 day
});

Ext.application({
	name: 'DVelum',
	launch: function() {
		app.application = this;
		app.content.addDocked(app.menu);
		app.viewport = Ext.create('Ext.container.Viewport', {
			cls:'formBody',
			layout: {
				type: 'vbox',
				pack: 'start',
				align: 'stretch'
			},
			items:[app.header, app.content]
		});
	}
});
