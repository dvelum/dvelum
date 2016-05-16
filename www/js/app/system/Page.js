Ext.ns('app.crud.page');
app.crud.page.themes = [];

Ext.define('app.crud.page.Window',{
    extend:'app.contentWindow',
    textPanel:null,
    blocksPanel:null,
    linkComponents:null,
    mainTab:null,
    canEdit:false,
    canPublish:false,
    canDelete:false,
    controllerUrl:null,

    constructor: function(config) {
        config = Ext.apply({
            title: appLang.CONTENT + ' :: ' + appLang.EDIT_ITEM,
            width: app.checkWidth(900),
            height:app.checkHeight(800),
            objectName:'page',
            blocksPanel:null,
            linkComponents:null,
            controllerUrl:app.root
        }, config || {});
        this.callParent(arguments);
    },
    initComponent:function(){

        this.textPanel = Ext.create('app.medialib.HtmlPanel',{
            title:appLang.TEXT,
            editorName:'text'
        });

        this.blocksPanel = Ext.create('app.blocksPanel',{
            dataId:this.dataItemId,
            title:appLang.BLOCKS_MAPPING,
            fieldName:'blocks',
            controllerUrl: this.controllerUrl,
            canEdit:this.canEdit
        });

        this.mainTab = Ext.create('Ext.Panel',{
            title:appLang.GENERAL,
            frame:false,
            border:false,
            layout:'anchor',
            bodyPadding:'3px',
            bodyCls:'formBody',
            anchor: '100%',
            scrollable:true,
            fieldDefaults: {
                labelAlign: 'right',
                labelWidth: 160,
                anchor: '100%'
            },
            items:[
                {
                    name:"parent_id",
                    xtype:"hidden",
                    value:0
                },{
                    allowBlank: false,
                    fieldLabel:appLang.PAGE_CODE,
                    name:"code",
                    vtype:"alphanum",
                    xtype:"textfield",
                    enableKeyEvents:true,
                    validateOnBlur :false,
                    listeners:{
                        'keyup' : {
                            fn: this.checkCode,
                            scope:this,
                            buffer:400
                        }
                    }
                },{
                    allowBlank: false,
                    fieldLabel:appLang.MENU_TITLE,
                    name:"menu_title",
                    xtype:"textfield"
                },{
                    allowBlank: false,
                    fieldLabel:appLang.PAGE_TITLE,
                    name:"page_title",
                    xtype:"textfield"
                },
                {
                    allowBlank: false,
                    fieldLabel:appLang.HTML_TITLE,
                    name:"html_title",
                    xtype:"textfield"
                },{
                    fieldLabel:appLang.SHOW_BLOCKS,
                    name:"show_blocks",
                    xtype:"checkbox",
                    inputValue:1
                },{
                    fieldLabel:appLang.USE_DEFAULT_BLOCKS_MAP,
                    name:"default_blocks",
                    xtype:"checkbox",
                    inputValue:1,
                    listeners:{
                        change:{
                            fn:this.defaultBlocksCheck,
                            scope:this
                        }
                    }
                },{
                    fieldLabel:appLang.IN_SITE_MAP,
                    name:"in_site_map",
                    xtype:"checkbox",
                    inputValue:1
                },{
                    displayField:"title",
                    queryMode:"local",
                    triggerAction:"all",
                    valueField:"id",
                    allowBlank: false,
                    fieldLabel:appLang.THEME,
                    name:"theme",
                    xtype:"combo",
                    store:Ext.create('Ext.data.Store',{
                        model:app.comboStringModel,
                        data:Ext.clone(app.crud.page.themes),
                        sorters:[ {
                            property: 'title',
                            direction: 'ASC' // or 'DESC' (case sensitive for local sorting)
                        }]
                    }),
                    listeners:{
                        select:{
                            fn:function(cmp){
                                this.blocksPanel.loadConfig(cmp.getValue());
                            },
                            scope:this
                        }
                    }
                },
                {
                    fieldLabel:appLang.META_KEYWORDS,
                    name:"meta_keywords",
                    width: 250,
                    xtype:"textarea"
                },{
                    fieldLabel:appLang.META_DESCRIPTION,
                    name:"meta_description",
                    width: 250,
                    xtype:"textarea"
                },{
                    displayField:"title",
                    queryMode:"local",
                    triggerAction:"all",
                    valueField:"id",
                    allowBlank: true,
                    fieldLabel:appLang.ATTACHED_FN,
                    name:"func_code",
                    width: 250,
                    xtype:"combo",
                    store:Ext.create('Ext.data.Store',{
                        model:app.comboStringModel,
                        /*proxy:{
                         type:'json',
                         simpleSortMode:true
                         },*/
                        sorters:[ {
                            property: 'title',
                            direction: 'ASC' // or 'DESC' (case sensitive for local sorting)
                        }],
                        data:aFuncCodes
                    })
                }
            ]});

        this.items = [this.mainTab , this.textPanel,this.blocksPanel];

        this.linkedComponents = [this.blocksPanel];

        this.callParent();

        var me = this;

        this.on('dataLoaded' , function(result){
            if(me.isFixed){
                me.editForm.getForm().findField('code').setReadOnly(true);
                me.unpublishBtn.hide();
                me.deleteBtn.hide();
            }else{
                me.editForm.getForm().findField('code').setReadOnly(false);
                me.deleteBtn.show();
            }
            me.updateLayout();
        }, me);
    },
    /**
     * Use default block Map check
     * @param field
     * @param value
     */
    defaultBlocksCheck:function(field, value){
        if(value){
            this.blocksPanel.disable();
        }else{
            this.blocksPanel.enable();
        }
    },
    /**
     * Check if page code is unique
     * @param {Ext.form.Field} field
     * @param {object} event
     */
    checkCode: function(field  , event)
    {
        var val = field.getValue();

        Ext.Ajax.request({
            url: this.controllerUrl + "checkcode",
            method: 'post',
            params:{
                'id':this.editForm.getForm().findField('id').getValue(),
                'code':val
            },
            success: function(response, request) {
                response =  Ext.JSON.decode(response.responseText);
                if(response.success){
                    field.setValue(response.data.code);
                    field.unsetActiveError();
                }else{
                    field.markInvalid(response.msg);
                    field.setActiveError(response.msg);
                }
            },
            failure:function() {
                Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
            }
        });

    }
});

Ext.define('app.crud.page.Model', {
    extend: 'Ext.data.Model',
    fields: [
        {name:'id' , type:'integer'},
        {name:'parent_id' , type:'integer'},
        {name:'code' , type:'string'},
        {name:'menu_title' , type:'string'},
        {name:'published' , type:'boolean'},
        {name:'date_created' , type:'date' ,dateFormat:"Y-m-d H:i:s"},
        {name:'date_updated' , type:'date' ,dateFormat:"Y-m-d H:i:s"},
        {name:'user' , type:'string'},
        {name:'updater' , type:'string'},
        {name:'last_version' , type:'integer'},
        {name:'published_version' , type:'integer'}
    ]
});

Ext.define('app.crud.page.Tree',{
    extend:'Ext.tree.Panel',
    controllerUrl:null,

    constructor:function(config){
        config = Ext.apply({
            rootVisible:false,
            useArrows: true
        }, config || {});
        this.callParent(arguments);
    },

    initComponent:function(){
        this.store = Ext.create('Ext.data.TreeStore',{
            proxy: {
                type: 'ajax',
                url:this.controllerUrl + 'treelist',
                reader: {
                    type: 'json',
                    idProperty: 'id'
                }
            },
            root: {
                text:appLang.ROOT,
                expanded: true,
                id:0
            }
        });


        this.collapseBtn = Ext.create('Ext.Button',{
            icon:app.wwwRoot + 'i/system/collapse-tree.png',
            tooltip:appLang.COLLAPSE_ALL,
            listeners:{
                click:{
                    fn:function(){
                        this.collapseAll();
                        this.collapseBtn.disable();
                        this.expandBtn.enable();
                    },
                    scope:this
                }
            }
        });
        this.expandBtn = Ext.create('Ext.Button',{
            tooltip:appLang.EXPAND_ALL,
            icon:app.wwwRoot + 'i/system/expand-tree.png',
            disabled:true,
            listeners:{
                click:{
                    fn:function(){
                        this.expandAll();
                        this.collapseBtn.enable();
                        this.expandBtn.disable();
                    },
                    scope:this
                }
            }
        });

        this.tbar = [this.collapseBtn , this.expandBtn];
        this.callParent(arguments);
    }
});

/**
 * Page list component
 * @author Kirill Egorov 2011
 * @extend Ext.Panel
 */
Ext.define('app.crud.page.Panel',{
    extend:'Ext.Panel',
    dataTree:null,
    dataStore:null,
    dataGrid:null,
    searchField:null,
    addSubButton:null,
    canEdit:false,
    canDelete:false,
    canPublish:false,
    controllerUrl:null,

    constructor: function(config) {
        config = Ext.apply({
            layout:'border'
        }, config || {});
        this.callParent(arguments);
    },

    initComponent:function(){
        /*
         * Pages tree
         */
        this.dataTree = Ext.create('app.crud.page.Tree',{
            region:'west',
            width:250,
            minWidth:100,
            split:true,
            controllerUrl:this.controllerUrl,
            viewConfig:{
                plugins: {
                    ptype: 'treeviewdragdrop'
                },
                listeners:{
                    drop:{
                        fn:this.sortChanged,
                        scope:this
                    }
                }
            }
        });


        var columnsConfig = [];


        if(this.canEdit)
        {
            columnsConfig.push(
                {
                    xtype:'actioncolumn',
                    align:'center',
                    width:40,
                    items:[
                        {
                            tooltip:appLang.EDIT_RECORD,
                            iconCls:'editIcon',
                            width:30,
                            align:'center',
                            scope:this,
                            handler:function(grid, rowIndex, colIndex){
                                this.showPageEdit(grid.getStore().getAt(rowIndex).get('id'),0);
                            }
                        }
                    ]
                });
        }


        columnsConfig.push({
            sortable: true,
            text:appLang.STATUS,
            dataIndex: 'published',
            width:60,
            align:'center',
            renderer:app.publishRenderer
        },{
            text:appLang.VERSIONS_HEADER,
            dataIndex:'id',
            align:'center',
            width:200,
            renderer:app.versionRenderer
        },{
            text:appLang.PAGE_CODE,
            dataIndex:'code',
            width:110,
            sortable: true
        },{
            text:appLang.TITLE,
            dataIndex:'menu_title',
            width:200,
            sortable: true,
            flex:1
        },{
            text:appLang.CREATED_BY,
            dataIndex:'user',
            width:200,
            renderer:app.creatorRenderer
        },{
            text:appLang.UPDATED_BY,
            dataIndex:'updater',
            width:200,
            renderer:app.updaterRenderer
        });

        if(this.canDelete){
            columnsConfig.push({
                xtype:'actioncolumn',
                align:'center',
                width:40,
                items:[
                    {
                        tooltip:appLang.DELETE_RECORD,
                        iconCls:'deleteIcon',
                        width:30,
                        align:'center',
                        scope:this,
                        handler:function(grid, rowIndex, colIndex){
                            var record = grid.getStore().getAt(rowIndex);
                            this.deleteRecord(record);
                        }
                    }
                ]
            });
        }


        this.dataStore = Ext.create('Ext.data.Store', {
            model: 'app.crud.page.Model',
            proxy: {
                type: 'ajax',
                url:this.controllerUrl +  'list',
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
                property : 'code',
                direction: 'DESC'
            }]
        });

        this.dataGrid = Ext.create('Ext.grid.Panel', {
            columns:columnsConfig,
            store:this.dataStore,
            region:'center',
            viewConfig:{
                stripeRows:false,
                enableTextSelection: true
            },
            frame: false,
            loadMask:true,
            columnLines: true,
            scrollable:true
        });

        this.searchField = Ext.create('SearchPanel',{
            store:this.dataStore,
            fieldNames:['code','menu_title'],
            local:true
        });

        var buttons=[];


        this.addButton = Ext.create('Ext.Button',{
            iconCls:'newpageIcon',
            text:appLang.ADD_PAGE,
            listeners:{
                click:{
                    fn:function(){
                        this.showPageEdit(0 , 0);
                    },
                    scope:this
                }
            }
        });

        this.addSubButton = Ext.create('Ext.Button',{
            iconCls:'subpageIcon',
            text:appLang.ADD_SUBPAGE,
            disabled:true,
            listeners:{
                click:{
                    fn:function(){

                        var sm = this.dataGrid.getSelectionModel();
                        if(!sm.hasSelection()){
                            this.addSubButton.disable();
                            return;
                        }
                        this.showPageEdit(0,sm.getSelection()[0].get('id'));
                    },
                    scope:this
                }
            }
        });

        this.defaultBlockMapButton = Ext.create('Ext.Button',{
            iconCls:'blockmapIcon',
            text:appLang.DEFAULT_BLOCK_MAP,
            scope:this,
            handler:this.showDefaultBlockmap
        });


        if(this.canEdit){
            buttons = [this.addButton, this.addSubButton,'-',this.defaultBlockMapButton,'-',this.searchField];
        }else{
            buttons=[this.searchField];
        }

        this.tbar = buttons;

        this.dataGrid.on('itemdblclick' , function(view , record , item , index , event , options){
            this.showPageEdit(record.get('id'));
        },this);

        this.dataTree.on('itemdblclick' , function(view, record, element , index , e , eOpts){
            this.showPageEdit(record.get('id'));
        },this);

        this.dataTree.getSelectionModel().on('selectionchange',function(sm, selected, options){
            if(!sm.hasSelection()){
                return;
            }
            this.searchField.clearFilter();
            var rec = selected[0];
            var index = this.dataStore.getById(parseInt(rec.get('id')));
            if(index!=undefined){
                this.dataGrid.getSelectionModel().select(index , false , false);
            }
        },this);

        this.dataGrid.getSelectionModel().on('selectionchange',function(sm,selected,options){
            if(sm.hasSelection()){
                this.addSubButton.enable();
            }else{
                this.addSubButton.disable();
                return
            }
            var rec = selected[0];
            var node = this.dataTree.getStore().getNodeById(''+rec.get('id'));
            if(node!=null){
                this.dataTree.getSelectionModel().select(node , false , true);
            }
        },this);

        this.items=[this.dataTree , this.dataGrid];
        this.callParent(arguments);

    },
    /**
     * Show page edit window
     * @param integer id - page id
     * @param integer parentId - parent page id
     */
    showPageEdit: function(id , parentId){
        var win = Ext.create('app.crud.page.Window', {
            dataItemId:id,
            canDelete:this.canDelete,
            canEdit:this.canEdit,
            canPublish:this.canPublish,
            controllerUrl:this.controllerUrl
        });
        win.on('dataSaved' , this.refreshData , this);
        win.show();

        if(!id && parentId){
            win.editForm.getForm().findField('parent_id').setValue(parentId);
        }

    },
    /**
     * Reload pages Tree and Grid
     */
    refreshData: function(){
        this.dataGrid.getStore().load();
        this.refreshPagesTree();
    },
    /**
     * Reload pages tree
     */
    refreshPagesTree:function(){
        this.dataTree.getStore().getRootNode().removeAll();
        this.dataTree.getStore().load();
    },
    /**
     * Change sort order of tree elements
     */
    sortChanged: function( node, data,  overModel,  dropPosition, options){

        if(!this.canEdit){
            return;
        }
        var parentNode = null;
        if(dropPosition == 'append'){
            parentNode = overModel;
        }else{
            parentNode = overModel.parentNode;
        }

        var childsOrder = [];
        parentNode.eachChild(function(node){
            childsOrder.push(node.getId());
        },this);

        Ext.Ajax.request({
            url: this.controllerUrl + 'sortpages',
            method: 'post',
            params:{
                'id':data.records[0].get('id'),
                'newparent':parentNode.get('id'),
                'order[]' : childsOrder
            },
            success: function(response, request) {
                response =  Ext.JSON.decode(response.responseText);
                if(response.success){
                    return;
                }else{
                    Ext.Msg.alert(appLang.MESSAGE, response.msg);
                }
            },
            failure: app.formFailure
        });
    },
    showDefaultBlockmap:function(){

        var blockMap = Ext.create('app.blocksPanel',{
            dataId:0,
            title:'',
            fieldName:'blocks',
            controllerUrl: this.controllerUrl,
            canEdit:this.canEdit
        });

        var win = Ext.create('Ext.Window',{
            items:[blockMap] ,
            layout:'fit',
            title:appLang.DEFAULT_BLOCK_MAP,
            modal:true,
            width: app.checkWidth(900),
            height:app.checkHeight(800),
            buttons:[
                {
                    text:appLang.SAVE,
                    scope:this,
                    hidden:!this.canEdit,
                    handler:function(){this.saveDefaultBlocks(blockMap);}
                },{
                    text:appLang.CLOSE,
                    handler:function(){this.up('window').close();}
                }
            ]
        });

        Ext.Ajax.request({
            url: this.controllerUrl + 'defaultblocks',
            method: 'post',
            success: function(response, request) {
                response =  Ext.JSON.decode(response.responseText);
                if(response.success){
                    blockMap.loadConfig('default');
                    blockMap.setData(response.data);
                    win.show();
                }else{
                    Ext.Msg.alert(appLang.MESSAGE, response.msg);
                }
            },
            failure: function(){
                Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
            }
        });

    },
    /**
     * Save default block map
     * @param {app.blocksPanel} blockMap
     */
    saveDefaultBlocks:function(blockMap){
        blockMap.mask(appLang.SAVING);
        Ext.Ajax.request({
            url: this.controllerUrl + 'defaultblockssave',
            method: 'post',
            params:blockMap.collectData(),
            success: function(response, request) {
                response =  Ext.JSON.decode(response.responseText);
                if(response.success){


                }else{
                    Ext.Msg.alert(appLang.MESSAGE, response.msg);
                }
                blockMap.unmask();
            },
            failure: function(){
                Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
                blockMap.unmask();
            }
        });
    },
    /**
     * Delete page record
     * @param {Ext.data.Record}
     */
    deleteRecord:function(record){
        var me = this;
        Ext.Msg.confirm(appLang.CONFIRM, appLang.MSG_CONFIRM_DELETE + ' "' + record.get('code')+'"' , function(btn){
            if(btn != 'yes'){
                return false;
            }
            Ext.Ajax.request({
                url: me.controllerUrl + 'delete',
                method: 'post',
                params:{
                    'id':record.get('id')
                },
                success: function(response, request) {
                    response =  Ext.JSON.decode(response.responseText);
                    if(response.success){
                        me.dataStore.remove(record);
                        me.refreshPagesTree();

                    }else{
                        Ext.Msg.alert(appLang.MESSAGE, response.msg);
                    }
                },
                failure: function(){
                    Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
                    blockMap.unmask();
                }
            });
        });
    }
});
