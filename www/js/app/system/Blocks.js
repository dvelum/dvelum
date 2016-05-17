Ext.ns('app.crud.blocks');

Ext.define('app.crud.blocks.ListModel', {
    extend: 'Ext.data.Model',
    fields: [
        {name:'id' , type:'integer'},
        {name:'title' , type:'string'},
        {name:'date_created', type:'date' , dateFormat:'Y-m-d H:i:s'},
        {name:'date_updated',type:'date' , dateFormat:'Y-m-d H:i:s'},
        {name:'user',type:'string' },
        {name:'updater',type:'string'},
        {name:'published' , type:'boolean'},
        {name:'published_version' , type:'integer'},
        {name:'is_system' , type:'boolean'},
        {name:'sys_name', type:'string'},
        {name:'params',type:'string'},
        {name:'last_version' , type:'integer'}
    ]
});


app.crud.blocks.ClassesStore  = Ext.create('Ext.data.Store',{
    model:'app.comboStringModel',
    proxy: {
        type: 'ajax',
        url:'',
        reader: {
            type: 'json',
            rootProperty: 'data',
            idProperty: 'id'
        },
        simpleSortMode: true
    },
    remoteSort: false,
    autoLoad: false,
    sorters: [{
        property : 'title',
        direction: 'DESC'
    }]
});

app.crud.blocks.MenuStore  = Ext.create('Ext.data.Store',{
    model:'app.comboModel',
    proxy: {
        type: 'ajax',
        url:'',
        reader: {
            type: 'json',
            rootProperty: 'data',
            idProperty: 'id'
        },
        simpleSortMode: true
    },
    remoteSort: false,
    autoLoad: false,
    sorters: [{
        property : 'title',
        direction: 'DESC'
    }]
});


/**
 * Item edit window
 */
Ext.define('app.crud.blocks.Window',{
    extend:'app.contentWindow',
    textPanel:null,
    mainTab:null,
    controllerUrl:null,
    constructor: function(config) {
        config = Ext.apply({
            title: appLang.BLOCKS + ' :: ' + appLang.EDIT_ITEM,
            width: 720,
            height:640,
            objectName:'blocks',
            hasPreview:false
        }, config);

        this.callParent(arguments);
    },
    initComponent:function()
    {
        this.mainTab = Ext.create('Ext.Panel',{
            title:appLang.GENERAL,
            frame:false,
            border:false,
            layout:'anchor',
            bodyPadding:'3px',
            bodyCls:'formBody',
            anchor: '100%',
            fieldDefaults: {
                labelAlign: 'right',
                labelWidth: 160,
                anchor: '100%'
            },
            items:[	{
                fieldLabel:appLang.TITLE,
                allowBlank:false,
                name:"title",
                anchor:'100%',
                xtype:"textfield"
            },{
                fieldLabel:appLang.SHOW_TITLE,
                name:"show_title",
                xtype:"checkbox",
                inputValue:1
            },{
                fieldLabel:appLang.ATTACHED_FN,
                name:'is_system',
                xtype:'checkbox',
                inputValue:1,
                listeners:{
                    change:{
                        fn:this.selectBlocktype,
                        scope:this
                    }
                }
            },{
                fieldLabel:appLang.BLOCK_CLASS,
                name:'sys_name',
                xtype:'combobox',
                triggerAction: 'all',
                anchor:'100%',
                queryMode: 'local',
                store:app.crud.blocks.ClassesStore,
                valueField: 'id',
                displayField: 'id',
                allowBlank:false,
                forceSelection:true,
                hidden:true,
                disabled:true
            },{
                fieldLabel:appLang.IS_MENU,
                name:'is_menu',
                xtype:'checkbox',
                inputValue:1,
                hidden:true,
                disabled:true,
                listeners:{
                    change:{
                        fn:this.selectMenutype,
                        scope:this
                    }
                }
            },{
                fieldLabel:appLang.MENU,
                name:'menu_id',
                xtype:'combobox',
                editable:true,
                triggerAction: 'all',
                anchor:'100%',
                queryMode: 'local',
                store:app.crud.blocks.MenuStore,
                valueField: 'id',
                displayField: 'title',
                allowBlank:false,
                forceSelection:true,
                hidden:true,
                disabled:true
            },{
                fieldLabel:appLang.BLOCK_PARAMS,
                name:'params',
                xtype:'textfield',
                hidden:true
            }
            ]});

        this.textPanel = Ext.create('app.medialib.HtmlPanel',{
            title:appLang.TEXT,
            editorName:'text'
        });

        this.items = [this.mainTab,this.textPanel];
        this.callParent();
    },
    /**
     * Rebuild interface
     */
    selectBlocktype:function(){
        var form = this.editForm.getForm();
        var system = form.findField('is_system').getValue();

        if(system){
            form.findField('sys_name').show();
            form.findField('sys_name').enable();
            form.findField('params').show();
            form.findField('is_menu').show();
            form.findField('is_menu').enable();
        }else{
            form.findField('sys_name').hide();
            form.findField('sys_name').disable();
            form.findField('params').hide();
            form.findField('is_menu').hide();
            form.findField('is_menu').disable();
        }
    },
    /**
     * Show / hide menu selector
     */
    selectMenutype:function(){
        var form = this.editForm.getForm();
        var menu = form.findField('is_menu').getValue();
        if(menu){
            form.findField('menu_id').enable();
            form.findField('menu_id').show();
        }else{
            form.findField('menu_id').hide();
            form.findField('menu_id').disable();
        }
    }
});

Ext.define('app.crud.blocks.Main',{
    extend: 'Ext.Panel' ,
    /**
     * @var {Ext.grid.Panel}
     */
    dataGrid:null,
    /**
     * @var {Ext.data.Store}
     */
    dataStore:null,
    /**
     * @var {searchBar}
     */
    searchField: null,
    /**
     * @var {Ext.Button}
     */
    addItemBtn: null,

    canEdit:false,
    canDelete:false,
    controllerUrl:null,

    constructor: function(config) {
        config = Ext.apply({
            layout:'fit'
        }, config || {});
        this.callParent(arguments);
    },
    initComponent:function(){

        this.dataStore = Ext.create('Ext.data.Store', {
            model: 'app.crud.blocks.ListModel',
            proxy: {
                type: 'ajax',
                url:this.controllerUrl +  'list',
                reader: {
                    type: 'json',
                    rootProperty: 'data',
                    totalProperty: 'count',
                    idProperty: 'id'
                },
                startParam:'pager[start]',
                limitParam:'pager[limit]',
                sortParam:'pager[sort]',
                directionParam:'pager[dir]',
                simpleSortMode: true
            },
            pageSize: 50,
            remoteSort: true,
            autoLoad: true,
            sorters: [{
                property : 'date_updated',
                direction: 'DESC'
            }]
        });

        this.searchField =  Ext.create('SearchPanel',{store:this.dataStore,local:false});

        this.addItemBtn = Ext.create('Ext.Button',{
            text:appLang.ADD_ITEM,
            hidden:(!this.canEdit)
        });

        this.addItemBtn.on('click' , function(){
            this.showPageEdit(0);
        } , this);


        this.dataGrid = Ext.create('Ext.grid.Panel',{
            store: this.dataStore,
            viewConfig:{
                stripeRows:false
            },
            frame: false,
            loadMask:true,
            columnLines: true,
            scrollable:true,
            tbar:[this.addItemBtn ,'-', '->'  , this.searchField],
            columns: [
                {
                    id: 'published',
                    sortable: true,
                    text:appLang.STATUS,
                    dataIndex: 'published',
                    width:50,
                    align:'center',
                    renderer:app.publishRenderer
                },{
                    text:appLang.VERSIONS_HEADER,
                    dataIndex:'id',
                    align:'center',
                    width:150,
                    renderer:app.versionRenderer
                },{
                    dataIndex:'is_system',
                    text:appLang.ATTACHED_FN,
                    width:140,
                    align:'center',
                    renderer:app.checkboxRenderer
                },{
                    id: 'title',
                    sortable: true,
                    text: appLang.TITLE,
                    dataIndex: 'title',
                    flex:1
                },{
                    text:appLang.CREATED_BY,
                    dataIndex:'date',
                    sortable: true,
                    width:210,
                    renderer:app.creatorRenderer
                },{
                    text:appLang.UPDATED_BY,
                    dataIndex:'update_date',
                    sortable: true,
                    width:210,
                    renderer:app.updaterRenderer
                }
            ],
            bbar: Ext.create('Ext.PagingToolbar', {
                store: this.dataStore,
                displayInfo: true,
                displayMsg: appLang.DISPLAYING_RECORDS + ' {0} - {1} '+appLang.OF+' {2}',
                emptyMsg:appLang.NO_RECORDS_TO_DISPLAY
            }),
            listeners : {
                'itemdblclick':{
                    fn:function(view , record , number , event , options){
                        this.showPageEdit(record.get('id'));
                    },
                    scope:this
                }
            }
        });
        this.items = [this.dataGrid];
        this.callParent();
    },
    /**
     * Show item edit window
     * @param integer id
     */
    showPageEdit: function(id){
        var win = Ext.create('app.crud.blocks.Window',{
            dataItemId:id,
            canDelete:this.canDelete,
            canEdit:this.canEdit,
            canPublish:this.canPublish,
            controllerUrl:this.controllerUrl
        });
        win.on('dataSaved' , function(){this.dataStore.load();}, this);
        win.show();
    }
});
