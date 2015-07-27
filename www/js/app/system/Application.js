app.application = false;
app.content =  Ext.create('Ext.Panel',{
	frame:false,
	border:false,
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

Ext.state.Manager.setProvider(new Ext.state.LocalStorageProvider());
app.cookieProvider = new Ext.state.CookieProvider({
	expires: new Date(new Date().getTime()+(1000*60*60*24)) //1 day
});

Ext.application({
	name: 'DVelum',
	menuStateKey:'_mState',
	launch: function() {
		app.application = this;
		app.menu = this.buildMenu(app.menuData);
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
		app.viewport.updateLayout();
	},
	buildMenu:function(menuData){
		var menuButtons = [];
		Ext.each(menuData,function(item){

			if(!developmentMode && item.dev){
				return;
			}
			menuButtons.push({
				xtype:'button',
				tooltip:item.title,
				href:item.url,
				hrefTarget:'_self',
				//text:'<img src="'+item.icon+'" width="32" height="32"/> ',
				text:'<img src="'+item.icon+'" width="14" height="14"/> ' + item.title,
				textAlign:'left'
			});
		});
		return Ext.create('Ext.Panel', {
			dock:'left',
			animCollapse:false,
			frame:false,
			border:false,
			stateEvents: ['expand', 'collapse'],
			stateful: true,
			stateId:this.menuStateKey,
			title:appLang.MENU,
			collapseDirection:'left',
			collapsible:true,
			lbar:menuButtons,
			enableOverflow:true
		});
	}
});
/**
 * 			    collapsed:false,

 */