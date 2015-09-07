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

app.cookieProvider = new Ext.state.CookieProvider({
	expires: new Date(new Date().getTime()+(1000*60*60*24)) //1 day
});

Ext.define('app.menuPanel',{
	extend:'Ext.Panel',
	isVertical:true,
	menuData:false,
	frame:false,
	border:false,
	devMode:false,
	menuCollapsed:false,
	initComponent:function(){
		this.isVertical ? this.dock='left' : this.dock='top';
		this.toolbarContainer = Ext.create('Ext.toolbar.Toolbar',{
			enableOverflow:true,
			dock:this.dock
		});
		this.dockedItems = [this.toolbarContainer];
		this.callParent();
		if(this.stateful){
			this.on('staterestore', function(menu,state,eOpts){
				this.menuCollapsed = state.menuCollapsed;
				this.showButtons();
			},this);
		}
		this.showButtons();
	},
	showButtons:function(){
		this.toolbarContainer.removeAll();
		var menuButtons = [];
		var	menuTriggerIcon = app.wwwRoot + 'i/system/right-btn.gif';
		var menuHandler = this.expandMenu;
		if(!this.menuCollapsed){
		    menuTriggerIcon = app.wwwRoot + 'i/system/left-btn.gif';
			menuHandler = this.collapseMenu
		}

		menuButtons.push({
			iconAlign:'right',
			textAlign:'right',
			width:22,
			maxWidth:22,
			icon:  menuTriggerIcon,
			scope:this,
			handler:menuHandler
		});

		Ext.each(this.menuData,function(item){
			if(!this.devMode && item.dev){
				return;
			}
			if(this.menuCollapsed){
				menuButtons.push({
					tooltip:item.title,
					href:item.url,
					hrefTarget:'_self',
					text:'<img src="'+item.icon+'" width="32" height="32"/> ',
					textAlign:'left'
				});
			}else{
				menuButtons.push({
					xtype:'button',
					tooltip:item.title,
					href:item.url,
					hrefTarget:'_self',
					text:'<img src="'+item.icon+'" width="14" height="14"/> ' + item.title,
					textAlign:'left'
				});
			}
		},this);

		this.toolbarContainer.add(menuButtons);
	},
	collapseMenu:function(){
		this.menuCollapsed = true;
		this.showButtons();
		this.fireEvent('menuCollapsed');
	},
	expandMenu:function(){
		this.menuCollapsed = false;
		this.showButtons();
		this.fireEvent('menuExpanded');
	},
	getState:function(){
		return {menuCollapsed:this.menuCollapsed};
	}
});

Ext.application({
	name: 'DVelum',
	launch: function() {
		app.application = this;
		app.menu = Ext.create('app.menuPanel',{
			menuData:app.menuData,
			isVertical:true,
			devMode:developmentMode,
			stateEvents: ['menuCollapsed', 'menuExpanded'],
			stateful: true,
			stateId:'_appMenuState'
		});
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
