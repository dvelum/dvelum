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
            if(!Ext.isEmpty(this.modules[id].isDesigner)){
                win.setTitle(this.modules[id].title);
            }
            win.show().toFront();
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

Ext.define('app.cls.menuPanel',{
    extend:'Ext.view.View',
    menuData:false,
    multiSelect: false,
    trackOver: true,
    itemSelector: 'div.menu-item-wrap',
    overItemCls: 'menu-item-over',
    width: '80%',
    height: '90%',
    initComponent:function(){
        this.store =  Ext.create('Ext.data.Store', {
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
   maximizable:true
});

Ext.application({
    name: 'DVelum',
    launch: function() {
        app.application = this;

        app.loader = Ext.create('app.cls.moduleLoader',{});

        app.menu = Ext.create('app.cls.menuPanel',{
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

        app.viewport = Ext.create('Ext.container.Viewport', {
            cls:'formBody',
            layout: 'fit',
            items:[{
                xtype:'container',
                layout:'center',
                items:app.menu
            }]
        });
    }
});
