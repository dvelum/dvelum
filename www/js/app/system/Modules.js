Ext.ns('app.crud.modules');

Ext.define('app.crud.modules.FrontendModel',{
    extend:'Ext.data.Model',
    fields: [
        {name:'class',type:'string'},
        {name:'code', type:'string'},
        {name:'title', type:'string'},
        {name:'dist', type:'boolean'}
    ],
    idProperty:'code'
});

/**
 * @event completeEdit value
 */
Ext.define('app.crud.modules.interfaceField',{
    extend:'Ext.form.FieldContainer',
    alias:'widget.interfaceField',
    dataField:null,
    triggerButton:null,
    value:'',
    name:'',
    controllerUrl:'',
    anchor:'100%',
    flex:1,
    layout:'fit',
    initComponent:function(){
        var  me = this;

        this.dataField = Ext.create('Ext.form.field.Text',{
            flex:1,
            anchor:'100%',
            bodyPadding:2,
            value:this.value,
            name:this.name,
            listeners:{
                click:{
                    fn:me.showSelectorWindow,
                    scope:me
                }
            }
        });

        this.triggerButton = Ext.create('Ext.button.Button',{
            iconCls:'urltriggerIcon',
            width:25,
            scope:me,
            handler:me.showSelectorWindow
        });
        this.items = [
            {
                xtype:'container',
                style:{
                    width:'100%'
                },
                flex: 1,
                anchor:'100%',
                layout: {
                    type: 'hbox'
                },
                items:[
                    this.dataField,
                    this.triggerButton,
                    {
                        xtype: 'button',
                        iconCls: 'deleteIcon',
                        scope: me,
                        width: 25,
                        handler: function () {
                            me.setValue('');
                        }
                    }
                ]
            }
        ];
        this.callParent();
    },
    showSelectorWindow:function()
    {
        var me = this;
        Ext.create('app.filesystemWindow',{
            title:appLang.SELECT_INTERFACE_PROJECT,
            viewMode:'select',
            controllerUrl:app.createUrl([this.controllerUrl ]),
            listeners:{
                scope: me,
                fileSelected:{
                    fn:function(value , record){
                        me.setValue(record.get('id'));
                        me.fireEvent('completeEdit', me.getValue() , me);
                    },
                    scope:this
                }
            }
        }).show();
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
    },
    clearInvalid:function(){
        return true;
    }
});

Ext.define('app.crud.modules.Model',{
    extend:'Ext.data.Model',
    fields: [
        {name:'id',type:'string'},
        {name:'class',type:'string'},
        {name:'dev' , type:'boolean'},
        {name:'active', type:'boolean'},
        {name:'title', type:'string'},
        {name:'designer',type:'string'},
        {name:'in_menu' , type:'boolean'},
        {name:'dist', type:'boolean'},
        {name:'related_files', type:'string'},
        {name:'icon', type:'string'},
        {name:'iconUrl', type:'string'}
    ]
});

Ext.define('app.crud.modules.BackendStore',{
    extend:'Ext.data.Store',
    model:app.crud.modules.Model,
    autoLoad:false
});

Ext.define('app.crud.modules.ControllersStore',{
    extend:'Ext.data.Store',
    model:'app.comboStringModel',
    autoLoad:true,
    constructor: function(config){
        config = Ext.apply({
            proxy:{
                type: 'ajax',
                extraParams:config.extraParams || {},
                api: {
                    read: config.controllerUrl + 'controllers'
                },
                reader: {
                    type: 'json',
                    rootProperty: 'data',
                    idProperty: 'id'
                },
                simpleSortMode: true
            },
            sorters: [{
                property : 'title',
                direction: 'ASC'
            }]
        }, config || {});
        this.callParent(arguments);
    }
});

Ext.define('app.crud.modules.EditBackendWindow',{
    extend:'app.editWindow',
    extraParams:null,
    controllerUrl: null,
    useTabs:false,
    hideEastPanel:true,
    editAction:'update',
    showToolbar:false,
    maximizable:false,
    initComponent:function(){

        this.items = [
            {
                xtype:'textfield',
                name:'title',
                fieldLabel:appLang.TITLE
            },
            {
                displayField:"title",
                queryMode:"local",
                triggerAction:"all",
                forceSelection:false,
                valueField:"id",
                allowBlank: false,
                xtype:"combo",
                fieldLabel:appLang.CONTROLLER,
                store:Ext.create('app.crud.modules.ControllersStore',{
                    controllerUrl: this.controllerUrl,
                    extraParams: this.extraParams
                }),
                name:'class'
            },{
                xtype:'interfaceField',
                controllerUrl:this.controllerUrl,
                fieldLabel:appLang.DESIGNER_PROJECT,
                name:'designer',
                anchor:'100%',
                width:this.width
            },
            {
                xtype:'iconselectfield',
                name:'icon',
                fieldLabel:appLang.ICON,
                anchor:'100%',
                wwwRoot:app.wwwRoot,
                prependWebRoot:true,
                controllerUrl:this.controllerUrl,
                width:this.width

            },
            { fieldLabel:appLang.ENABLED, name: 'active', xtype:'checkbox'},
            { fieldLabel:appLang.DEVELOPMENT, name: 'dev', xtype:'checkbox'},
            { fieldLabel:appLang.IN_MENU, name: 'in_menu', xtype:'checkbox'}
        ];
        this.callParent();
    }
});

Ext.define('app.crud.modules.EditFrontendWindow',{
    extend:'app.editWindow',
    extraParams:null,
    controllerUrl: null,
    useTabs:false,
    hideEastPanel:true,
    editAction:'update',
    showToolbar:false,
    maximizable:false,
    primaryKey:'id',
    initComponent:function(){

        this.items = [
            {
                xtype:'textfield',
                name:'title',
                fieldLabel:appLang.TITLE
            },
            {
                displayField:"title",
                queryMode:"local",
                triggerAction:"all",
                forceSelection:false,
                valueField:"id",
                allowBlank: false,
                xtype:"combo",
                fieldLabel:appLang.CONTROLLER,
                store:Ext.create('app.crud.modules.ControllersStore',{
                    controllerUrl: this.controllerUrl,
                    extraParams: this.extraParams
                }),
                name:'class'
            },{
                xtype:'textfield',
                controllerUrl:this.controllerUrl,
                fieldLabel:appLang.CODE,
                name:'code',
                anchor:'100%',
                width:this.width
            }
        ];
        this.callParent();
    }
});

Ext.define('app.crud.modules.toolsPlugin',{
    extend: 'Ext.Editor',
    shim: false,
    labelSelector: 'modulesBtn',
    autoSize: {
        width: 'boundEl'
    },
    //bubbleEvents:['toolClick'],
    init: function(view) {
        this.view = view;
        this.mon(view, 'afterrender', function(){
            this.mon(this.view.getEl(), {
                click: {
                    fn: this.onClick,
                    scope: this
                }
            });
        }, this);
    },
    // on mousedown show editor
    onClick: function(e, target) {
        var me = this;
        var	item, record;
        var node = Ext.fly(target);
        if (node.hasCls(me.labelSelector)) {
            e.stopEvent();
            item = me.view.findItemByChild(target);
            record = me.view.store.getAt(me.view.indexOf(item));
            this.fireEvent('toolClick' , record , node);
        }
    }
});

/**
 * @event dataSaved
 */
Ext.define('app.crud.modules.backendView',{
    extend:'Ext.container.Container',
    scrollable:false,
    dataView:null,
    layout: {
        type: 'vbox',
        align : 'stretch',
        pack  : 'start'
    },
    canEdit:false,
    canDelete:false,

    initComponent:function(){
        var me = this;

        var itemTpl = new Ext.XTemplate(
            '<tpl for=".">',
            '<div class="module-wrap">',
            '<div class="tools" align="right">',
            '<img class="modulesBtn" action-type="edit" src="'+app.wwwRoot+'i/system/edit.png" data-qtip="'+appLang.EDIT+'">',
            '<tpl if="dist == false">',
            '<img class="modulesBtn" action-type="delete" src="'+app.wwwRoot+'i/system/delete.png" data-qtip="'+appLang.DELETE+'">',
            '</tpl>',
            '</div>',
            '<div class="title">{title}</div>',
            '<span class="controller">{class}</span>',
            '<div class="icon"><img src="{iconUrl}" title="{title:htmlEncode}"></div>',
            '</div>',
            '</tpl>'
        );

        this.dataView = Ext.create('Ext.view.View', {
            store: this.dataStore,
            tpl:itemTpl,
            trackOver: true,
            overItemCls: 'x-item-over',
            itemSelector: 'div.module-wrap',
            singleSelect: true,
            bodyCls:'formBody',
            plugins:[
                Ext.create('app.crud.modules.toolsPlugin', {
                    dataIndex: 'id',
                    listeners:{
                        toolClick:{
                            fn:function(record,target){
                                switch(target.getAttribute('action-type')){
                                    case 'edit' :
                                        this.editModule(record);
                                        break;
                                    case 'delete':
                                        this.deleteModule(record);
                                        break;
                                }
                            },
                            scope:this
                        }
                    }
                })
            ],
            listeners: {
                render: {
                    fn:function(v){
                        this.initDragZone(v);
                        this.initDropZone(v);
                    },
                    scope:me
                },
                itemdblclick:{
                    fn:function(view, record, item, index, e, eOpts){
                        this.editModule(record);
                    },
                    scope:me
                }
            }
        });

        this.items = [
            {
                xtype:'panel',
                html:'<div align="center"><h3>'+this.panelLabel+'</h3></div>',
                border:false,
                frame:false
            },{
                xtype:'panel',
                bodyCls:'module-target',
                layout:'fit',
                flex:1,
                items:this.dataView,
                scrollable :'y',
                border:false,
                frame:false
            }
        ];



        this.callParent();
    },
    initDragZone:function(v){
        var me = this;
        v.dragZone = Ext.create('Ext.dd.DragZone', v.getEl(), {
            getDragData: function(e) {
                var sourceEl = e.getTarget(v.itemSelector, 10), d;
                if (sourceEl) {
                    d = sourceEl.cloneNode(true);
                    d.id = Ext.id();
                    return (v.dragData = {
                        sourceEl: sourceEl,
                        repairXY: Ext.fly(sourceEl).getXY(),
                        ddel: d,
                        record: v.getRecord(sourceEl),
                        sourceCmp:me
                    });
                }
            },
            getRepairXY: function() {
                return this.dragData.repairXY;
            }
        });
    },
    initDropZone:function(v){
        var me = this;

        v.dropZone = Ext.create('Ext.dd.DropZone', v.el, {
            // If the mouse is over a target node, return that node. This is
            // provided as the "target" parameter in all "onNodeXXXX" node event handling functions
            getTargetFromEvent: function(e) {
                return e.getTarget('.module-target');
            },
            // On entry into a target node, highlight that node.
            onNodeEnter : function(target, dd, e, data){
                Ext.fly(target).addCls('module-target-hover');
            },

            // On exit from a target node, unhighlight that node.
            onNodeOut : function(target, dd, e, data){
                Ext.fly(target).removeCls('module-target-hover');
            },

            // While over a target node, return the default drop allowed class which
            // places a "tick" icon into the drag proxy.
            onNodeOver : function(target, dd, e, data){

                // here we can check if drop is allowed
                if(data.sourceCmp == me){
                    return false;
                }
                return true;
            },

            //  On node drop, we can interrogate the target node to find the underlying
            //  application object that is the real target of the dragged data.
            //  In this case, it is a Record in the GridPanel's Store.
            //  We can use the data set up by the DragZone's getDragData method to read
            //  any data we decided to attach.
            onNodeDrop : function(target, dd, e, data){

                if(data.sourceCmp == me){
                    return false;
                }
                data.sourceCmp.dataStore.remove(data.record);
                var recData = Ext.apply(data.record.data,me.recordOptions);
                var rec = me.dataStore.add(recData);
                me.saveRecord(rec[0] , data.sourceCmp);
                return true;
            }
        });
    },
    /**
     * Save module record
     * @param record
     * @param sourceCmp
     */
    saveRecord:function(record , sourceCmp){
        var me = this;
        Ext.Ajax.request({
            url: me.controllerUrl + "update",
            method: 'post',
            params:Ext.apply(this.extraParams || {}, record.getData(false)),
            scope:me,
            success: function(response, request) {
                response =  Ext.JSON.decode(response.responseText);
                if(response.success){
                    return;
                }else{
                    Ext.Msg.alert(appLang.MESSAGE, response.msg);
                    me.revertRecord(record , sourceCmp);
                }
            },
            failure:function() {
                Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
                me.revertRecord(record , sourceCmp);
            }
        });
    },
    /**
     * Revert changes
     * @param record
     * @param sourceCmp
     */
    revertRecord:function(record , sourceCmp){
        this.dataStore.remove(record);
        Ext.apply(record.data, sourceCmp.recordOptions);
        sourceCmp.dataStore.add(record);
    },
    /**
     * Show module editor
     * @param record
     */
    editModule:function(record){
        var w = Ext.create('app.crud.modules.EditBackendWindow',{
            modal:true,
            dataItemId:record.get('id'),
            controllerUrl:this.controllerUrl,
            extraParams:this.extraParams,
            title:record.get('id'),
            width:580,
            height:450,
            canDelete:this.canDelete,
            canEdit:this.canEdit,
            resizable:false
        });
        w.on('dataSaved',function(){
            this.fireEvent('dataSaved');
            w.close();
        },this);
        w.show();
    },
    /**
     * Show delete module dialog
     * @param record
     */
    deleteModule:function(record){
        var me = this;
        var win = Ext.create('app.crud.modules.DeleteWindow',{
            moduleId:record.get('id'),
            controllerUrl:this.controllerUrl,
            title:appLang.REMOVE_MODULE + ' "' + record.get('title')+'"',
            relatedFiles:record.get('related_files')
        });

        win.on('deleteItems' , function(deleteRelated){
            Ext.Ajax.request({
                url: this.controllerUrl + 'delete',
                method: 'post',
                params:Ext.apply(this.extraParams || {}, {
                    id:record.get('id'),
                    delete_related:deleteRelated
                }),
                success: function(response, request) {
                    response =  Ext.JSON.decode(response.responseText);
                    if(response.success){
                        me.dataStore.remove(record);
                        win.close();
                    }else{
                        Ext.Msg.alert(appLang.MESSAGE, response.msg);
                    }
                },
                failure: function(){
                    Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
                }
            });
        },this);
        win.show();
    }
});

Ext.define('app.crud.modules.Backend',{
    extend:'Ext.panel.Panel',
    controllerUrl:'',
    canEdit:false,
    canDelete:false,
    productionStore: null,
    developmentStore: null,
    disabledStore: null,
    extraParams:null,
    productionView: null,
    developmentView: null,
    disabledView: null,
    layout: {
        type: 'hbox',
        pack: 'start',
        align: 'stretch'
    },
    initComponent:function(){

        if(this.canEdit){
            this.tbar = [
                {
                    iconCls:'newdocIcon',
                    text:appLang.ADD_ITEM,
                    scope:this,
                    handler:this.addAction
                },'-',{
                    iconCls:'newdocIcon',
                    text:appLang.GENERATE_MODULE,
                    scope:this,
                    handler:this.createAction
                },'-',{
                    icon:app.wwwRoot + "i/system/build.png",
                    text:appLang.REBUILD_CLASS_MAP,
                    scope:this,
                    handler:this.rebuildClassMap
                }
            ];
        }


        this.productionStore = Ext.create('app.crud.modules.BackendStore');
        this.developmentStore = Ext.create('app.crud.modules.BackendStore');
        this.disabledStore = Ext.create('app.crud.modules.BackendStore');

        this.productionView = Ext.create('app.crud.modules.backendView',{
            panelLabel:appLang.ACTIVE,
            controllerUrl:this.controllerUrl,
            flex:1,
            dataStore:this.productionStore,
            recordOptions:{active:true,dev:false},
            extraParams:this.extraParams,
            canDelete:this.canDelete,
            canEdit:this.canEdit,
            listeners:{
                dataSaved:{
                    fn:this.loadData,
                    scope:this
                }
            }
        });

        this.developmentView = Ext.create('app.crud.modules.backendView',{
            panelLabel:appLang.DEVELOPMENT,
            controllerUrl:this.controllerUrl,
            flex:1,
            dataStore:this.developmentStore,
            recordOptions:{active:true,dev:true},
            extraParams:this.extraParams,
            canDelete:this.canDelete,
            canEdit:this.canEdit,
            listeners:{
                dataSaved:{
                    fn:this.loadData,
                    scope:this
                }
            }
        });

        this.disabledView = Ext.create('app.crud.modules.backendView',{
            panelLabel:appLang.DISABLED,
            controllerUrl:this.controllerUrl,
            flex:1,
            dataStore:this.disabledStore,
            recordOptions:{active:false,dev:true},
            extraParams:this.extraParams,
            canDelete:this.canDelete,
            canEdit:this.canEdit,
            listeners:{
                dataSaved:{
                    fn:this.loadData,
                    scope:this
                }
            }
        });

        this.dataStore =  Ext.create('Ext.data.Store' , {
            model:app.crud.modules.Model,
            autoLoad:true,
            autoSave:false,
            proxy:{
                type: 'ajax',
                url: this.controllerUrl+ 'list',
                extraParams:this.extraParams,
                reader: {
                    type: 'json',
                    rootProperty: 'data',
                    idProperty: 'id'
                },
                simpleSortMode: true
            },
            sorters: [{
                property : 'title',
                direction: 'ASC'
            }],
            listeners: {
                load: {
                    fn: function (store, records) {
                        this.disabledStore.removeAll();
                        this.developmentStore.removeAll();
                        this.productionStore.removeAll();
                        Ext.each(records, function (item, index) {
                            if (!item.get('active')) {
                                this.disabledStore.add(item);
                                return;
                            }
                            if (item.get('dev')) {
                                this.developmentStore.add(item);
                                return;
                            }
                            this.productionStore.add(item);
                        }, this);
                    },
                    scope: this
                }
            }
        });

        this.items = [
            this.productionView,
            this.developmentView,
            this.disabledView
        ];

        this.callParent();

    },
    loadData:function(){
        this.dataStore.load();
    },
    addAction:function(){
        var me = this;
        var w = Ext.create('app.crud.modules.EditBackendWindow',{
            modal:true,
            dataItemId:0,
            controllerUrl:this.controllerUrl,
            extraParams:this.extraParams,
            title:'',
            width:580,
            height:450,
            canDelete:this.canDelete,
            canEdit:this.canEdit,
            resizable:false
        });
        w.on('dataSaved',function(){
            me.loadData();
            w.close();
        },this);
        w.show();
    },
    createAction:function(){

        var win = Ext.create('app.crud.modules.CreateWindow',{
            controllerUrl:this.controllerUrl,
            title:appLang.NEW_MODULE
        });

        win.on('dataSaved',function(data){
            this.loadData();
        },this);

        win.show();
    },
    rebuildClassMap:function(btn){
        var oldText = btn.getText();
        btn.setText(' <img src="'+app.wwwRoot+'i/system/ajaxload.gif" height="14">');
        Ext.Ajax.request({
            url: app.createUrl([app.admin ,'modules' , 'rebuildmap']),
            method: 'post',
            timeout:240000,
            success: function(response, request) {
                response =  Ext.JSON.decode(response.responseText);
                if(!response.success){
                    Ext.Msg.alert(appLang.MESSAGE, response.msg);
                }
                btn.setText(oldText);
            },
            failure:function(){
                Ext.Msg.alert(appLang.MESSAGE, appLang.CANT_EXEC);
                btn.setText(oldText);
            }
        });
    }
});

Ext.define('app.crud.modules.Frontend',{
    extend:'Ext.grid.Panel',
    controllerUrl:'',
    viewConfig:{
        stripeRows:true,
        enableTextSelection:true
    },
    selModel: {
        selType: 'cellmodel'
    },
    canEdit:false,
    canDelete:false,
    loadMask:true,
    columnLines: true,
    scrollable:true,
    extraParams:null,
    initComponent:function(){

        this.store =  Ext.create('Ext.data.Store' , {
            model:'app.crud.modules.FrontendModel',
            autoLoad:true,
            autoSave:false,
            proxy:{
                type: 'ajax',
                url: this.controllerUrl+ 'list',
                extraParams:this.extraParams,
                reader: {
                    type: 'json',
                    rootProperty: 'data'
                },
                simpleSortMode: true
            },
            sorters: [{
                property : 'title',
                direction: 'ASC'
            }]
        });

        var columns = [];

        if(this.canEdit){
            columns.push(
                {
                    xtype:'actioncolumn',
                    width:30,
                    align:'center',
                    items:[
                        {
                            iconCls:'editIcon',
                            tooltip:appLang.EDIT_ITEM,
                            width:30,
                            scope:this,
                            handler:function(grid , row , col){
                                var store = grid.getStore();
                                this.editModule(store.getAt(row));
                            }
                        }
                    ]
                }
            );
            this.tbar = [{
                text:appLang.ADD_ITEM,
                iconCls:'newdocIcon',
                scope:this,
                handler:this.addModule
            }];

        }

        columns.push({
                text: appLang.CODE,
                dataIndex: 'code',
                width: 200
            },{
                text:appLang.CONTROLLER,
                dataIndex:'class',
                align:'left',
                width:170
            },{
                text: appLang.TITLE,
                dataIndex: 'title',
                width:200,
                align:'left',
                flex:1
            }
        );

        if(this.canDelete){
            columns.push(
                {
                    xtype:'actioncolumn',
                    width:30,
                    align:'center',
                    items:[
                        {
                            iconCls:'deleteIcon',
                            tooltip:appLang.DELETE,
                            width:30,
                            scope:this,
                            handler:function(grid , row , col){
                                var store = grid.getStore();
                                this.removeModule(store.getAt(row));
                            },
                            isDisabled:function(v,r,c,i,record){
                                return record.get('dist');
                            }
                        }
                    ]
                });


        }
        this.columns = columns;
        this.callParent(arguments);

        if(this.canEdit){
            this.on('rowdblclick',function(grid,record){
                this.editModule(record);
            },this);
        }
    },
    addModule:function(){
        var w = Ext.create('app.crud.modules.EditFrontendWindow',{
            modal:true,
            dataItemId:'',
            controllerUrl:this.controllerUrl,
            extraParams:this.extraParams,
            title:'',
            width:500,
            height:340,
            canDelete:this.canDelete,
            canEdit:this.canEdit,
            resizable:false
        });
        w.on('dataSaved',function(){
            this.getStore().load();
            w.close();
        },this);
        w.show();
    },
    editModule:function(record){
        var w = Ext.create('app.crud.modules.EditFrontendWindow',{
            modal:true,
            dataItemId:record.get('code'),
            controllerUrl:this.controllerUrl,
            extraParams:this.extraParams,
            title:record.get('code'),
            width:500,
            height:340,
            canDelete:this.canDelete,
            canEdit:this.canEdit,
            resizable:false
        });
        w.on('dataSaved',function(){
            this.getStore().load();
            w.close();
        },this);
        w.show();
    },
    /**
     * Remove module
     */
    removeModule:function(record){
        Ext.Ajax.request({
            url: this.controllerUrl + "delete",
            method: 'post',
            params:Ext.apply(this.extraParams,{code:record.get('code')}),
            scope:this,
            success: function(response, request) {
                response =  Ext.JSON.decode(response.responseText);
                if(response.success){
                    this.getStore().remove(record);
                }else{
                    Ext.Msg.alert(appLang.MESSAGE, response.msg);
                }
            },
            failure:function() {
                Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
            }
        });
    }
});

Ext.define('app.crud.modules.Main',{
    extend:'Ext.tab.Panel',
    canEdit:false,
    canDelete:false,
    controllerUrl:'',

    initComponent:function(){

        this.adminTab = Ext.create('app.crud.modules.Backend',{
            title:appLang.BACKEND_MODULES,
            controllerUrl:this.controllerUrl,
            canEdit:this.canEdit,
            canDelete:this.canDelete,
            extraParams:{type:'backend'},
            scrollable:true
        });

        this.frontendTab = Ext.create('app.crud.modules.Frontend',{
            title:appLang.FRONTEND_MODULES,
            controllerUrl:this.controllerUrl,
            canEdit:this.canEdit,
            canDelete:this.canDelete,
            extraParams:{type:'frontend'}
        });

        this.items = [this.adminTab , this.frontendTab];

        this.callParent();
    }
});

/**
 * Module generator window
 * @event dataSaved
 * @params data
 */
Ext.define('app.crud.modules.CreateWindow',{
    extend:'Ext.Window',
    modal:true,
    resizable:false,
    width:400,
    height:120,
    layout:'fit',
    dataForm:null,
    controllerUrl:'',

    initComponent:function(){

        this.dataForm = Ext.create('Ext.form.Panel',{
            bodyCls:'formBody',
            bodyPadding:5,
            fieldDefaults:{
                labelAlign:'right',
                labelWidth:90,
                anchor:'100%'
            },
            items:[
                {
                    xtype:'combobox',
                    name:'object',
                    fieldLabel:appLang.OBJECT,
                    queryMode:'local',
                    valueField:'id',
                    forceSelection:true,
                    displayField:'title',
                    allowBlank:false,
                    store:Ext.create('Ext.data.Store',{
                        model:'app.comboStringModel',
                        proxy: {
                            type: 'ajax',
                            url:this.controllerUrl + 'objects',
                            reader: {
                                type: 'json',
                                rootProperty: 'data',
                                idProperty: 'id'
                            },
                            simpleSortMode: true
                        },
                        autoLoad: true,
                        sorters: [
                            {
                                property : 'title',
                                direction: 'ASC'
                            }
                        ]
                    })
                }
            ]
        });

        this.buttons = [
            {
                text:appLang.GENERATE_MODULE,
                scope:this,
                handler:this.createModule
            },
            {
                text:appLang.CANCEL,
                scope:this,
                handler:this.close
            }
        ];
        this.items = [this.dataForm];
        this.callParent();
    },
    createModule:function()
    {
        var handle = this;

        this.dataForm.getForm().submit({
            clientValidation: true,
            waitMsg:appLang.SAVING,
            method:'post',
            url:this.controllerUrl + 'create',
            success: function(form, action) {
                if(!action.result.success){
                    Ext.Msg.alert(appLang.MESSAGE, action.result.msg);
                } else{
                    handle.fireEvent('dataSaved' , action.result.data);
                    handle.close();
                }
            },
            failure: app.formFailure
        });
    }
});

/**
 * @event dataSaved
 * @params boolean deleteRelated
 */
Ext.define('app.crud.modules.DeleteWindow',{
    extend:'Ext.Window',
    modal:true,
    width:400,
    height:200,
    moduleId:false,
    controllerUrl:false,
    relatedFiles:false,
    layout:'fit',

    initComponent:function(){
        this.removeFiles = Ext.create('Ext.form.field.Checkbox',{
            name:'remove_related',
            boxLabel:appLang.REMOVE_MODULE_FILES +':',
            value:false
        });

        this.items = [
            {
                xtype:'form',
                bodyCls:'formBody',
                bodyPadding:2,
                items:[
                    this.removeFiles,
                    {
                        xtype:'displayfield',
                        value:this.relatedFiles
                    }
                ]
            }
        ];

        this.buttons = [
            {
                text:appLang.DELETE,
                scope:this,
                handler:function(){
                    this.fireEvent('deleteItems', this.removeFiles.getValue());
                }
            },{
                text:appLang.CANCEL,
                scope:this,
                handler:function(){
                    this.close();
                }
            }
        ];
        this.callParent();
    }
});
