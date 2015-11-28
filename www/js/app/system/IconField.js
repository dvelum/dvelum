Ext.ns('app');

Ext.define('app.iconFieldImageModel', {
    extend: 'Ext.data.Model',
    fields: [
        {name: 'name'},
        {name: 'url'},
        {name: 'path'}
    ]
});
/**
 * @event select
 */
Ext.define('app.iconField',{
    extend:'Ext.form.FieldContainer',
    alias:'widget.iconselectfield',
    triggerCls : 'urlTrigger',
    dataField:null,
    allowBlank:true,
    triggerButton:null,
    layout: 'hbox',
    controllerUrl:'',
    imageField:null,
    value:'',
    wwwRoot:'/',
    prependWebRoot:false,
    anchor:'100%',
    initComponent:function(){
        var  me = this;

        var selectedImage = null;
        if(this.value.length){
            selectedImage = this.wwwRoot + this.value;
        }

        this.imageField =  Ext.create('app.ImageField',{
            value:selectedImage,
            wwwRoot:this.wwwRoot,
            prependWebRoot:this.prependWebRoot
        });

        this.dataField = Ext.create('Ext.form.field.Text',{
            flex:1,
            name:this.name || '',
            value:this.value,
            allowBlank:this.allowBlank,
            listeners:{
                change:{
                    fn:function(field,value){
                        this.imageField.setValue(value);
                    },
                    scope:this
                }
            }
        });

        this.triggerButton = Ext.create('Ext.button.Button',{
            iconCls:'urltriggerIcon',
            width:25,
            scope:me,
            handler:function(){
                var win = Ext.create('app.iconSelectorWindow', {
                    width:600,
                    height:400,
                    controllerUrl:this.controllerUrl,
                    title:appLang.IMAGES,
                    listeners: {
                        scope: me,
                        select:function(url){
                            me.setValue(url);
                            me.fireEvent('select');
                        }
                    }
                });
                win.show();
            }
        });

        var rowItems = [this.dataField , this.triggerButton];

        if(this.allowBlank)
        {
            rowItems.push({
                xtype: 'button',
                iconCls: 'deleteIcon',
                scope: me,
                width: 25,
                handler: function () {
                    me.setValue(null);
                }
            });
        }

        this.items = [
            this.imageField ,
            {
                xtype:'container',
                anchor:'100%',
                layout: {
                    type: 'hbox'
                },
                items:rowItems
            }
        ];
        this.callParent();
    },
    setValue:function(value){
        this.dataField.setValue(value);
    },
    getValue:function(){
        return this.dataField.getValue();
    },
    reset:function(){
        this.dataField.reset();
    },
    isValid:function(){
        return true;
    }
});

/**
 *
 * @event select Fires when action is selected
 * @param string url
 *
 */
Ext.define('app.iconSelectorWindow',{
    extend:'Ext.Window',
    layout:'border',
    dataTree:null,
    dataView:null,
    viewPanel:null,
    controllerUrl:'',
    listAction:'iconDirs',
    imagesAction:'iconList',
    width:500,
    height:300,
    modal:true,
    iconWidth:64,
    iconHeight:64,
    initComponent:function(){

        this.dataTree = Ext.create('app.FilesystemTree',{
            controllerUrl:this.controllerUrl,
            listAction:this.listAction,
            region:'west',
            minWidth:250,
            width:250,
            collapsible:true,
            listeners:{
                'select':{
                    fn:function(RowModel, record, index, eOpts ){
                        var store = this.dataView.getStore();
                        store.proxy.setExtraParam('dir' , record.get('id'));
                        store.load();
                    },
                    scope:this
                }
            }
        });

        this.dataView =  Ext.create('Ext.view.View', {
            store: Ext.create('Ext.data.Store', {
                model: 'app.iconFieldImageModel',
                proxy: {
                    type: 'ajax',
                    url: this.controllerUrl + this.imagesAction,
                    reader: {
                        type: 'json',
                        rootProperty: 'data'
                    },
                    autoLoad:false
                }
            }),
            tpl: [
                '<tpl for=".">',
                '<div class="thumb-wrap" id="{name}">',
                '<div class="thumb" align="center"><img src="{url}" title="{name}" width="'+this.iconWidth+'" height="'+this.iconHeight+'"></div>',
                '<span class="x-editable">{shortName}</span></div>',
                '</tpl>',
                '<div class="x-clear"></div>'
            ],
            multiSelect: false,
            height: 310,
            trackOver: true,
            cls:'images-view',
            overItemCls: 'x-item-over',
            itemSelector: 'div.thumb-wrap',
            emptyText: appLang.noImagesToDisplay,
            prepareData: function(data) {
                Ext.apply(data, {
                    shortName: Ext.util.Format.ellipsis(data.name, 15)
                });
                return data;
            }
        });

        this.viewPanel = Ext.create('Ext.Panel',{
            region:'center',
            items:[this.dataView],
            frame: false,
            bodyCls:'formBody',
            scrollable:true
        });

        this.items = [this.dataTree , this.viewPanel];

        this.buttons = [
            {
                text:appLang.SELECT,
                scope:this,
                handler:this.onSelect
            },{
                text:appLang.CANCEL,
                scope:this,
                handler:this.close
            }
        ]

        this.callParent();
    },
    onSelect:function()
    {
        var sm = this.dataView.getSelectionModel();
        if(!sm.hasSelection()){
            Ext.Msg.alert(appLang.MESSAGE, desLang.selectIcon);
            return;
        }
        this.fireEvent('select',sm.getLastSelected().get('path'));
        this.close();
    }
});