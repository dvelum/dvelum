Ext.ns('app.home');

Ext.define('app.home.Model',{
    extend:'Ext.data.Model',
    fields:[
        {name:'id', type:'string'},
        {name:'icon', type:'string'},
        {name:'title', type:'string'},
        {name:'url' , type:'string'},
        {name:'itemCls', type:'string'}
    ],
    idProperty:'id'
});

Ext.define('app.home.Panel',{
    extend:'Ext.Panel',
    frame: false,
    //title:appLang.HOME,
    layout:'fit',
    dataStore:null,
    controllerUrl:null,
    initComponent:function(){

        this.dataStore = Ext.create('Ext.data.Store', {
            model: 'app.home.Model',
            proxy: {
                type: 'ajax',
                url: this.controllerUrl + 'list',
                reader: {
                    type: 'json',
                    rootProperty:'data'
                }
            },
            autoLoad:true
        });

        var itemTpl = new Ext.XTemplate(
            '<tpl for=".">',
            '<a href="{url}" target="_self"><div class="module-wrap home {itemCls}" align="center">',
            '<img src="{icon}" title="{title:htmlEncode}"/>',
            '<div class="title">{title}</div>',
            '</div></a>',
            '</tpl>'
        );

        this.items = Ext.create('Ext.view.View', {
            store: this.dataStore,
            tpl:itemTpl,
            multiSelect: true,
            height: 310,
            trackOver: true,
            overItemCls: 'x-item-over',
            itemSelector: 'div.module-wrap'
        });
        this.callParent();
    }
});


Ext.onReady(function(){
    app.content.add(Ext.create('app.home.Panel',{
        controllerUrl:app.admin + '/index/'
    }));
});