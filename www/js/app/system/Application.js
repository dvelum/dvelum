
app.content =  new Ext.Panel({
	region: 'center',
	frame:false,
	border:false,
	layout:'fit',
	margins: '0 5 0 0',
	autoScroll:false,
	items:[],
	collapsible:false
});

app.cookieProvider = new Ext.state.CookieProvider({
    expires: new Date(new Date().getTime()+(1000*60*60*24)) //1 day
});

app.saveMenuState = function(panel){
	app.cookieProvider.set('memuState',panel.id);
};

app.saveMenuPanelState = function(expanded){
	app.cookieProvider.set('memuPanelState',expanded);
};

app.menuPanelState = function(){
	var curState = app.cookieProvider.get('memuPanelState');
	if(typeof curState != undefined){
		return curState;
	}
	return true;
};

app.restoreMenuState = function(){
	var curState = app.cookieProvider.get('memuState');
	if(typeof curState != undefined)
	{
		if(Ext.getCmp(curState)){
			Ext.getCmp(curState).expand();
		}
	}
};
	
app.menu = Ext.create('Ext.panel.Panel',{
	region: 'west',
	title:appLang.MENU,
    split: true,
    width: 240,
    minSize: 185,
    maxSize: 400,
    collapsible: true,
    collapsed:!app.menuPanelState(),
	collapseMode:'header',
	collapseFirst:true,
    bodyCls:'formBody',
	cls: 'adminWestMenu',
    layout: 'accordion',
	header:	{
		tag: 'div',
		cls: 'sysMenuLogo'
	},
    listeners:{
    	'expand':{
    		fn:function(panel){
    			app.saveMenuPanelState(true);
    			//app.restoreMenuState();
    		}
    	},
    	'collapse':{
    		fn:function(panel){
    			app.saveMenuPanelState(false);
    		}
    	}
    },
    items: [
        {
            title: appLang.MAIN,
            itemId:'main',
            border: false,
            collapsible: true,
            contentEl:'mainMenu',
            iconCls: 'nav',
            frame:true,
            titleCollapse:true,
            autoScroll:true,
            listeners:{
            	'expand':{
            		fn:function(panel){
            			app.saveMenuState(panel);
            		}
            	}
            }
        },{
            title: appLang.SYSTEM_PREFERENCES,
            itemId:'system',
            hidden:true,
            border: false,
            collapsible: true,
            iconCls: 'nav',
            contentEl:'systemMenu',
            frame:true,
            collapsed :true,
            autoScroll:true,
            titleCollapse : true,
            listeners:{
            	'expand':{
            		fn:function(panel){
            			app.saveMenuState(panel);
            		}
            	}
            }
        }
     ]
});
		
app.header = new Ext.Panel({
	region:'north',
	contentEl:'header',
	bodyCls:'formBody',
	cls: 'adminHeader',
	height: 30
});
			
Ext.application({
    name: 'DVelum',
    launch: function() {
    	
		/*
		 * Global scope config variable
		 */
		if(developmentMode){
			app.menu.getComponent('system').show();
		}
		   	
    	app.viewport = Ext.create('Ext.container.Viewport', {
    		layout: 'border',
    		//renderTo: Ext.getBody(),
    		items:[app.header, app.content , app.menu]
        });
    	
    	if(app.menuPanelState()){
    		app.restoreMenuState();
    	}
    }
});