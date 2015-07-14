app.application = false;
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

app.cookieProvider = new Ext.state.CookieProvider({
	expires: new Date(new Date().getTime()+(1000*60*60*24)) //1 day
});

app.header = Ext.create('Ext.Panel',{
	region:'north',
	contentEl:'header',
	bodyCls:'formBody',
	cls: 'adminHeader',
	height: 30
});

Ext.application({
	name: 'DVelum',
	launch: function() {
		app.application = this;

		this.buildMenu(app.menuData);

		app.viewport = Ext.create('Ext.container.Viewport', {
			layout: 'border',
			items:[app.header, app.content , app.menu]
		});
	},
	buildMenu:function(menuData){

		var menuButtons = [];

		Ext.each(menuData,function(item){

			if(!developmentMode && item.dev){
				return;
			}

			menuButtons.push({
				tooltip:item.title,
				href:item.url,
				text:'<img src="'+item.icon+'" width="30" height="30"/>',
				textAlign:'left'
			});
		});

		app.menu = Ext.create('Ext.Panel',{
			region: 'west',
			scrollable:true,
			split: false,
			lbar: {
				items:menuButtons,
				enableOverflow:true,
				xtype:'toolbar'
			}
		});
	}
});