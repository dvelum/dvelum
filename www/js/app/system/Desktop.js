Ext.ns('app.cls','app.__modules');

app.application = false;

Ext.define('app.cls.moduleLoader',{
    extend:'Ext.Base',
    mixins:['Ext.mixin.Observable'],
    modules:{},
    loaded:[],
    loadModule:function(data){
        if(Ext.isEmpty(this.modules[data.id])){
            Ext.Ajax.request({
                url: app.createUrl([app.admin,'index','moduleInfo']),
                method: 'post',
                scope:this,
                params:{
                    id:data.id
                },
                success: function(response, request) {
                    response =  Ext.JSON.decode(response.responseText);
                    var me = this;
                    if(response.success){
                        me.modules[data.id] = response.data;

                        if(!Ext.isEmpty(response.data.layout.includes)){
                            me.loadScripts(response.data.layout.includes,function(){
                                me.showModule(data.id);
                            });
                        }

                    }else{
                        app.msg(appLang.MESSAGE,response.msg);
                    }
                },
                failure:function() {
                    Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
                }
            });
        }else{
            this.showModule(data.id);
        }
    },
    loadScripts:function(list , callback){

        var scriptCount = 0;

        if(!Ext.isEmpty(list.js)){
            scriptCount+= list.js.length;
        }

        if(!Ext.isEmpty(list.css)){
            scriptCount+= list.css.length;
        }

        var me = this;

        Ext.each(list.css, function(item, index){
            if(Ext.Array.contains(me.loaded , item)){
                scriptCount --;
                if(scriptCount==0){
                    callback();
                }
                return;
            }
            Ext.Loader.loadScript({
                url:item,
                onLoad:function(){
                    scriptCount --;
                    me.loaded.push(item);
                    if(scriptCount==0){
                        callback();
                    }
                }
            });
        },me);

        Ext.each(list.js, function(item, index){
            if(Ext.Array.contains(me.loaded , item)){
                scriptCount --;
                if(scriptCount==0){
                    callback();
                }
                return;
            }
            Ext.Loader.loadScript({
                url:item,
                onLoad:function(){
                    scriptCount --;
                    me.loaded.push(item);
                    if(scriptCount==0){
                        callback();
                    }
                }
            });
        },me);
    },
    showModule:function(id){
        if(!Ext.isEmpty(app.__modules[id])) {
            var win = app.__modules[id];
            if(!Ext.isEmpty(this.modules[id].layout.isDesigner)){
                win.setTitle(this.modules[id].layout.title);
            }
            app.desktop.add(win);
            win.show().toFront();
            win.on({'activate':{
                fn: app.desktop.updateActive
            }});
        }else{
            app.msg(appLang.ERROR,appLang.CANT_EXEC);
        }
    },
    /**
     * Get module permissions info
     * @param module
     * @returns {*}
     */
    getPermissions:function(module){
        if(!Ext.isEmpty(this.modules[module]) && !Ext.isEmpty(this.modules[module].permissions)){
            return this.modules[module].permissions;
        }else{
            return false;
        }
    }
});

app.cookieProvider = new Ext.state.CookieProvider({
    expires: new Date(new Date().getTime()+(1000*60*60*24)) //1 day
});

Ext.define('app.cls.desktopMenu',{
    extend:'Ext.view.View',
    menuData:false,
    multiSelect: false,
    padding: '20 0 0 0',
    trackOver: true,
    itemSelector: 'div.menu-item-wrap',
    overItemCls: 'menu-item-over',
    width: '80%',
    height: '90%',
    initComponent:function(){
        this.store =  Ext.create('Ext.data.Store',{
            fields: [
                {name:'id'},
                {name: 'title'},
                {name: 'url'},
                {name: 'icon'}
            ],
            data:this.menuData
        });

        this.tpl= [
            '<tpl for=".">',
            '<div class="menu-item-wrap">',
            '<div class="thumb"><img src="{icon}" title="{title}" alt="{title}"/></div><span>{title:htmlEncode}</span>',
            '</div>',
            '</tpl>'
        ];
        this.callParent();
    }
});

Ext.define('app.cls.ModuleWindow',{
    extend:'Ext.Window',
    layout:'fit',
    modal:false,
    width:app.checkWidth(1000),
    height:app.checkHeight(750),
    closeAction:'hide',
    constrainHeader: true,
    maximizable:true
});

Ext.define('app.cls.startMenu',{
    extend: 'Ext.menu.Menu',
    indent: false,
    menuData:[],
    initComponent:function(){
        this.callParent();
        this.fillMenu();
    },
    fillMenu: function(){
        Ext.each(this.menuData,function(item){
            this.add({
                tooltip:item.title,
                text:item.title,
                textAlign:'left',
                icon: item.icon,
                iconCls: 'menu-item-icon',
                record: item,
                handler: function(el){
                    var record = el.record;
                    if(!record.isLink){
                        app.loader.loadModule(record);
                    }else{
                        window.location = record.url;
                    }
                }
            });
        },this);
    }
});

Ext.define('app.cls.desktopHeader',{
    extend: 'Ext.Panel',
    startMenu: false,
    bodyCls:'formBody',
    cls: 'adminHeader',
    height: 30,
    layout: {
        type: 'hbox',
        pack: 'start',
        align: 'stretch'
    },
    initComponent: function(){
        this.callParent();
        this.fillPanel();
    },
    fillPanel: function(){
        var tbar = Ext.create('Ext.toolbar.Toolbar',{
            width: '100%',
            frame: false,
            border: false,
            margin: 0,
            padding: 0
        });
        var startBtn = Ext.create('Ext.button.Button',{
            height: 26,
            width: 100,
            menu: this.startMenu,
            tooltip: appLang.MODULES,
            html: '<img src="' + app.wwwRoot + 'i/logo-btn.png">'
        });
        var sysVer = Ext.create('Ext.Component',{
            autoEl: 'div',
            cls: 'sysVersion',
            html: '<span class="num">' + app.version + '</span>'
        });
        var loginInfo = Ext.create('Ext.Component',{
            xtype: 'component',
            autoEl: 'div',
            cls: 'sysVersion',
            html: '<div class="loginInfo">' + appLang.YOU_LOGGED_AS
            + ': <span class="name">' + app.user.name + '</span>'
            + '<span class="logout"><a href="' + app.admin + '?logout=1">'
            + '<img src="' + app.wwwRoot + 'i/system/icons/logout.png" title="'
            + appLang.LOGOUT + '" height="16" width="16"></a></span></div>'
        });
        tbar.add(startBtn);
        tbar.add(sysVer);
        tbar.add({xtype:'tbfill'});
        tbar.add(loginInfo);
        this.add(tbar);
    }
});

Ext.define('app.cls.desktop',{
    extend: 'Ext.Panel',
    frame:false,
    border:false,
    layout:'anchor',
    scrollable:true,
    items: [],
    desktopItems: [],
    collapsible:false,
    flex : 1,
    bodyCls: 'formBody',
    activeWin: null,
    initComponent: function(){
        this.items = [{
            xtype: 'container',
            layout: 'center',
            items: this.desktopItems
        }];
        this.callParent()
    },
    updateActive: function(win){
        win.el.focus();
    }
});

Ext.application({
    name: 'DVelum',
    launch: function() {
        app.application = this;
        app.loader = Ext.create('app.cls.moduleLoader',{});

        app.desktopMenu = Ext.create('app.cls.desktopMenu',{
            menuData:app.menuData,
            listeners:{
                itemclick:function(view, record, index, eOpts){
                    if(!record.get('isLink')){
                        app.loader.loadModule(record.getData());
                    }else{
                        window.location = record.get('url');
                    }
                }
            }
        });

        app.desktop =  Ext.create('app.cls.desktop',{
            desktopItems: app.desktopMenu
        });

        app.startMenu = Ext.create('app.cls.startMenu',{
            menuData: app.menuData
        });

        app.header = Ext.create('app.cls.desktopHeader',{
            startMenu: app.startMenu
        });

        app.viewport = Ext.create('Ext.container.Viewport',{
            cls:'formBody',
            layout: {
                type: 'vbox',
                pack: 'start',
                align: 'stretch'
            },
            items:[app.header,app.desktop]
        });
    }
});
