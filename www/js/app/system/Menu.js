Ext.ns('app.crud.menu');

Ext.define('app.crud.menu.LinkModel',{
    extend:'Ext.data.Model',
    fields:[
        {name:'title' , type:'string'},
        {name:'page_id', type:'integer'},
        {name:'published', type:'boolean'},
        {name:'url', type:'string'},
        {name:'resource_id', type:'integer'},
        {name:'link_type', type:'string'}
    ]
});

Ext.define('app.crud.menu.EditorPanel',{
    extend:'Ext.tree.Panel',
    menuId:0,
    fieldName:'data',
    controllerUrl:'',

    constructor:function(config){
        config = Ext.apply({
            rootVisible:false,
            useArrows: true,
            viewConfig:{
                plugins: {
                    ptype: 'treeviewdragdrop'
                }
            }
        }, config || {});
        this.callParent(arguments);
    },
    initComponent:function()
    {
        this.deleteBtn = Ext.create('Ext.Button',{
            iconCls:'deleteIcon',
            text:appLang.DELETE,
            handler:this.removeItem,
            scope:this,
            disabled:true
        });

        this.btnSubItem = Ext.create('Ext.button.Button',{
            text:appLang.ADD_SUBITEM,
            handler:this.addSubItem,
            disabled:true,
            scope:this
        });

        this.tbar = [
            {
                text:appLang.ADD_ITEM,
                handler:this.addItem,
                scope:this
            }, this.btnSubItem, {
                text:appLang.IMPORT_FROM_SITE_TREE,
                handler:this.importStructure,
                scope:this,
                iconCls:'treeIcon'
            },
            '->', this.deleteBtn
        ];

        this.store = Ext.create('Ext.data.TreeStore',{
            proxy: {
                type: 'memory',
                reader: {
                    type: 'json',
                    idProperty: 'id'
                }
            },
            fields:[
                {name:'id' , type:'string'},
                {name:'text', type:'string'},
                {name:'parent_id' , type:'integer'},
                {name:'published' , type:'boolean'},
                {name:'page_id', type:'integer'},
                {name:'url', type:'string'},
                {name:'resource_id', type:'integer'},
                {name:'link_type', type:'string'},
                {name:'iconCls', type:'string'}
            ],
            autoLoad:false,
            root: {
                text:appLang.ROOT,
                expanded: true,
                id:0
            }
        });
        this.callParent(arguments);

        this.on('itemdblclick' , function(view, record, element , index , e , eOpts){
            this.editItem(record);
        },this);

        this.getSelectionModel().on('selectionchange',function(sm){
            if(sm.hasSelection() && sm.getSelection()[0].getId()!=0){
                this.btnSubItem.enable();
                this.deleteBtn.enable();
            }else{
                this.btnSubItem.disable();
                this.deleteBtn.disable();
            }
        },this);
    },
    /**
     * Set data for Links Tree
     * @param {array} data
     */
    setData:function(data){

        if(!data.length){
            var newRoot = {
                text:appLang.ROOT,
                expanded: true,
                id:0,
                children:[]
            };
        }else{

            var newRoot = {
                text:appLang.ROOT,
                expanded: true,
                id:0,
                children:data
            };
        }

        this.setRootNode(newRoot);
    },
    collectData:function(){
        var recordsList = [];
        this.collectNodeItems(this.getRootNode(), recordsList);
        var result = {};
        result[this.fieldName] = Ext.JSON.encode(recordsList);
        return result;

    },
    /**
     * Collect nodes from Tree
     * @param node
     * @param result
     */
    collectNodeItems:function(node , result){
        var order = 0;
        node.eachChild(function(child){
            result.push({
                id:child.get('id'),
                title:child.get('text'),
//        		parent_id:child.get('parent_id'),
                parent_id:(child.parentNode == null) ? 0 : child.parentNode.get('id'),
                order:order,
                published:child.get('published'),
                page_id:child.get('page_id'),
                url:child.get('url'),
                resource_id:child.get('resource_id'),
                link_type:child.get('link_type')
            });

            if(child.hasChildNodes()){
                this.collectNodeItems(child , result);
            }
            order++;
        },this);
    },
    /**
     * Get last id in node (recursive)
     * @param node
     * @return integer id
     */
    getLastId:function(node){
        var id = parseInt(node.getId());
        if(node.hasChildNodes()){
            node.eachChild(function(child){
                var cId = this.getLastId(child);
                if(cId > id){
                    id = cId;
                }
            },this);
        }
        return id;
    },
    addSubItem:function(){
        this.addItemPriv(true);
    },
    addItem:function(){
        this.addItemPriv();
    },
    addItemPriv:function(sub){
        var sub = sub || false;
        var win = Ext.create('app.crud.menu.ItemWindow',{controllerUrl:this.controllerUrl});

        win.on('itemSelected',function(pageId , title , published , link_type , url , resource_id ){
            var sm = this.getSelectionModel();

            var iconCls = 'pageHidden';

            if(published){
                iconCls = 'pagePublic';
            }

            var newId = this.getLastId(this.getRootNode()) + 1;

            var newNode = {
                id:newId,
                parent_id:0,
                published:published,
                text:title,
                iconCls:iconCls,
                page_id:pageId,
                expanded:true,
                children:[],
                allowDrag:true,
                leaf:false,
                url:url,
                resource_id:resource_id,
                link_type:link_type
            };

            if(sub && sm.hasSelection()){
                var parentNode = sm.getSelection()[0];
                newNode.parent_id = parentNode.get('id');
                parentNode.appendChild(newNode);
            }else{
                this.getRootNode().appendChild(newNode);
            }
        },this);

        win.on('itemRemoved', function(id){
            this.remove(this.getStore().getNodeById(id) , true);
        },this);

        win.show();
    },
    /**
     * Edit Link
     * @param {Ext.data.record} record
     */
    editItem:function(record){

        var win = Ext.create('app.crud.menu.ItemWindow',{
            valueTitle:record.get('text'),
            valuePageId:record.get('page_id'),
            valuePublished:record.get('published'),
            valueLinkType:record.get('link_type'),
            valueUrl:record.get('url'),
            valueResourceId:record.get('resource_id'),
            nodeId:record.get('id')
        });

        win.on('itemSelected',function(pageId , title , published ,link_type , url , resource_id){
            var sm = this.getSelectionModel();

            var iconCls = 'pageHidden';

            if(published){
                iconCls = 'pagePublic';
            }

            record.set('published' , published);
            record.set('page_id' , pageId);
            record.set('text' , title);
            record.set('link_type' , link_type);
            record.set('url' , url);
            record.set('resource_id' , resource_id);
            record.set('iconCls' , iconCls);
            record.commit();

        },this);

        win.on('itemRemoved', function(id){
            this.remove(this.getStore().getNodeById(id) , true);
        },this);

        win.show();
    },
    /**
     * Remove selected menu link
     */
    removeItem:function(){
        var sm = this.getSelectionModel();
        if(!sm.hasSelection()){
            Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_SELECT_RECORD, false).toFront();
            return;
        }
        var node = sm.getSelection()[0];
        node.parentNode.removeChild(node , true);
    },
    /**
     * Import links from site structure
     */
    importStructure:function(){
        Ext.Ajax.request({
            url:this.controllerUrl + 'import',
            method: 'post',
            scope:this,
            success: function(response, request) {
                response =  Ext.JSON.decode(response.responseText);

                if(!response.success){
                    Ext.Msg.alert(appLang.MESSAGE , response.msg);
                } else {
                    this.setData(response.data);
                }
            },
            failure:app.ajaxFailure
        });
    }
});

/**
 *
 *
 * @event itemSelected
 * @param title
 * @param pageId
 * @param published
 *
 * @event itemRemoved
 * @param nodeId
 *
 */
Ext.define('app.crud.menu.ItemWindow',{
    extend:'Ext.Window',
    resizable:false,
    width:450,
    height:400,
    title:appLang.EDIT_ITEM,
    valueTitle:'',
    valuePageId:0,
    valuePublished:0,
    valueLinkType:'',
    valueResourceId:'',
    valueUrl:'',
    nodeId:0,
    layout:'fit',
    modal:true,
    controllerUrl:'',

    initComponent:function()
    {
        this.dataForm = Ext.create('Ext.form.Panel',{
            bodyCls:'formBody',
            scrollable:true,
            bodyPadding:5,
            fieldDefaults:{
                anchor:'100%'
            },
            defaults:{
                labelWidth:90
            },
            items:[
                {
                    xtype:'textfield' ,
                    name:'title',
                    fieldLabel:appLang.TITLE
                },
                {
                    xtype:'combobox',
                    name:'link_type',
                    fieldLabel:appLang.LINK_TYPE,
                    selectOnFocus:true,
                    editable:true,
                    triggerAction: 'all',
                    anchor:'100%',
                    queryMode: 'local',
                    store: Ext.create('Ext.data.Store',{
                        data:menuItemlinkTypes,
                        model:app.comboStringModel
                    }),
                    valueField: 'id',
                    value:'url',
                    displayField: 'title',
                    allowBlank:false,
                    forceSelection:true,
                    listeners:{
                        change:{
                            fn:this.selectLinktype,
                            scope:this
                        }
                    }
                },
                {
                    xtype:'combobox',
                    name:'page_id',
                    fieldLabel:appLang.PAGE,
                    selectOnFocus:true,
                    editable:true,
                    triggerAction: 'all',
                    anchor:'100%',
                    queryMode: 'local',
                    store: Ext.create('Ext.data.Store',{
                        model:'app.comboModel',
                        proxy: {
                            type: 'ajax',
                            url:this.controllerUrl + 'pagelist',
                            reader: {
                                type: 'json',
                                rootProperty: 'data',
                                idProperty: 'id'
                            },
                            simpleSortMode: true
                        },
                        remoteSort: false,
                        autoLoad: true,
                        sorters: [{
                            property : 'title',
                            direction: 'DESC'
                        }]
                    }),
                    valueField: 'id',
                    displayField: 'title',
                    allowBlank:true,
                    forceSelection:true,
                    hidden:true
                },
                {
                    name:'url',
                    fieldLabel:'URL',
                    xtype:'textfield'
                },{
                    xtype:'medialibitemfield',
                    resourceType:'all',
                    name:'resource_id',
                    hidden:true,
                    fieldLabel:appLang.RESOURCE
                },
                {
                    xtype:'checkbox',
                    name:'published',
                    fieldLabel:appLang.PUBLISHED
                }
            ]
        });

        this.buttons = [
            {
                text:appLang.APPLY,
                handler:this.saveItem,
                scope:this
            },{
                text:appLang.CLOSE,
                handler:this.close,
                scope:this
            }
        ];

        this.items = [this.dataForm];

        this.callParent();


        this.dataForm.loadRecord(
            Ext.create('app.crud.menu.LinkModel',{
                title:this.valueTitle,
                page_id:this.valuePageId,
                published:this.valuePublished,
                link_type:this.valueLinkType,
                url:this.valueUrl,
                resource_id:this.valueResourceId
            })
        );
    },
    selectLinktype:function()
    {
        var form = this.dataForm.getForm();
        var type = form.findField('link_type').getValue();

        var urlField = form.findField('url');
        var resField = form.findField('resource_id');
        var pageField = form.findField('page_id');

        switch(type){
            case 'resource':
                pageField.hide();
                resField.show();
                urlField.hide();
                break;
            case 'page' :
                pageField.show();
                resField.hide();
                urlField.hide();
                break;
            case 'nolink':
                resField.hide();
                urlField.hide();
                pageField.hide();
                break;
            default:
                pageField.hide();
                resField.hide();
                urlField.show();
        }
    },
    saveItem:function()
    {
        var form = this.dataForm.getForm();
        var pageId = form.findField('page_id').getValue();
        var pageTitle = form.findField('title').getValue();
        var published = form.findField('published').getValue();
        var link_type = form.findField('link_type').getValue();
        var url = form.findField('url').getValue();
        var resource_id = form.findField('resource_id').getValue();

        if(!pageTitle.length){
            return;
        }

        this.fireEvent('itemSelected', pageId, pageTitle , published , link_type , url , resource_id);
        this.close();
    }
});


Ext.define('app.crud.menu.EditWindow',{
    extend:'app.editWindow',
    development:false,
    controllerUrl:'',

    initComponent:function(){

        this.listItemsPanel = Ext.create('app.crud.menu.EditorPanel',{
            title:appLang.ITEMS,
            controllerUrl:this.controllerUrl
        });

        this.items = [
            Ext.create('Ext.Panel',{
                frame : false,
                title : appLang.GENERAL,
                bodyPadding : 3,
                bodyCls : "formBody",
                fieldDefaults : {
                    labelAlign : 'right',
                    anchor : '100%'
                },
                defaults:{
                    labelWidth:60
                },
                layout : "anchor",
                items:[
                    {
                        xtype:'textfield',
                        name:'code',
                        fieldLabel:appLang.CODE,
                        vtype:'alphanum'
                    },
                    {
                        xtype:'textfield',
                        name:'title',
                        fieldLabel:appLang.TITLE
                    }]
            }),
            this.listItemsPanel
        ];
        this.callParent();
        this.registerLink(this.listItemsPanel);
    }
});

/*
 * Main panel for menu module
 */
Ext.define('app.crud.menu.Panel',{

    extend:'Ext.grid.Panel',
    controllerUrl:'',
    canEdit:false,
    canDelete:false,
    columnLines:true,

    initComponent:function(){

        this.store = Ext.create('Ext.data.Store',{
            fields:[
                {name:'id' , type:'integer'},
                {name:'code' , type:'string'},
                {name:'title' , type:'string'}
            ],
            proxy:{
                type: 'ajax',
                url:this.controllerUrl + 'list',
                reader: {
                    type: 'json',
                    rootProperty: 'data',
                    idProperty: 'id'
                }
            },
            autoLoad: true,
            sorters: [{
                property : 'title',
                direction: 'ASC'
            }]
        });

        this.columns = [
            {
                dataIndex:'code',
                text:appLang.CODE,
                width:100
            },{
                dataIndex:'title',
                text:appLang.TITLE,
                flex:1
            }
        ];

        this.tbar = [];

        if(this.canEdit)
        {
            this.on('itemdblclick', function(view , record , number , event , options){
                this.showEdit(record.get('id'));
            },this);

            this.tbar.push(
                {
                    text:appLang.ADD_ITEM,
                    handler:function(){
                        this.showEdit(0);
                    },
                    scope:this
                }
            );
        }

        this.tbar.push('->');
        this.tbar.push(Ext.create('SearchPanel',{
            store:this.store,
            fieldNames:['code','title'],
            local:true
        }));

        this.callParent();
    },
    /**
     * Show Edit Window
     * @param id
     */
    showEdit:function(id){
        var win = Ext.create('app.crud.menu.EditWindow', {
            title:appLang.EDIT_ITEM,
            width:500,
            height:400,
            dataItemId:id,
            canDelete:this.canDelete,
            canEdit:this.canEdit,
            development:this.development,
            controllerUrl:this.controllerUrl,
            objectName:'menu',
            hideEastPanel:true
        });

        win.on('dataSaved' , function(){
            this.store.load();
        }, this);
        win.show();
    }
});
