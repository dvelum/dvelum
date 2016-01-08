Ext.ns('app.crud.acl');

Ext.define('app.crud.acl.Main',{
    extend:'Ext.tab.Panel',
    groupsList:null,
    canEdit:false,
    canDelete:false,
    groupsPanel:null,
    deferredRender:true,
    activeTab:0,
    controllerUrl:null,
    initComponent:function(){
        this.groupsPanel = Ext.create('app.crud.acl.Groups',{
            title:appLang.GROUPS,
            canEdit:this.canEdit,
            canDelete:this.canDelete,
            controllerUrl:this.controllerUrl
        });
        this.items=[this.groupsPanel];
        this.callParent();
    }
});

/**
 * Permissions pannel allows to modify users and groups permissions
 * {Ext.Panel}
 */
Ext.define('app.crud.acl.Permissions',{
    extend:'Ext.Panel',
    layout:'fit',
    /**
     * @var {Ext.grid.EditorGridPanel}
     */
    dataGrid:null,
    /**
     * @var {Ext.data.JsonStore}
     */
    dataStore:null,
    controllerUrl:null,

    initComponent:function(){

        this.dataStore = Ext.create('Ext.data.Store', {
            fields: [
                {name:'id'  , type:'integer'},
                {name:'user_id'  , type:'integer'},
                {name:'group_id' , type:'integer'},
                {name:'view', type:'boolean'},
                {name:'create', type:'boolean'},
                {name:'edit', type:'boolean'},
                {name:'delete', type:'boolean'},
                {name:'object' , type: 'string'},
                {name:'title' , type: 'string'},
                {name:'publish', type:'boolean'},
                {name:'rc', type:'boolean'}
            ],
            proxy: {
                type: 'ajax',
                reader: {
                    type: 'json',
                    rootProperty: 'data',
                    idProperty: 'id'
                },
                writer:{
                    type:'json',
                    writeAllFields:true,
                    encode: true,
                    listful:true,
                    rootProperty:'data'
                },
                extraParams:{
                    'user_id':0,
                    'group_id':0
                },
                method:'post',
                url: this.controllerUrl + 'permissions',
                simpleSortMode: true
            },
            sorters: [{
                property : 'module',
                direction: 'ASC'
            }]
        });

        var cellEditing = Ext.create('Ext.grid.plugin.CellEditing', {
            clicksToEdit: 1
        });


        this.saveBtn =  Ext.create('Ext.Button',{
            text:appLang.SAVE,
            iconCls:'saveIcon',
            handler:this.savePermissions,
            scope:this,
            tooltip:appLang.SAVE,
            hidden:!this.canEdit,
            disabled:true
        });

        this.dataGrid = Ext.create('Ext.grid.Panel', {
            store: this.dataStore,
            viewConfig:{
                stripeRows:true
            },
            frame: false,
            loadMask:true,
            columnLines: true,
            scrollable:true,
            clicksToEdit:1,
            selModel: {
                selType: 'cellmodel'
            },
            tbar:[this.saveBtn],
            columns:[
                {
                    text:appLang.OBJECT,
                    dataIndex:'object',
                    align:'left',
                    renderer:false,
                    editable:false,
                    id:'object',
                    width:250,
                    renderer:function(value, metaData, record, rowIndex, colIndex, store){
                        return value + ' ('+record.get('title')+')';
                    }
                },{
                    text:appLang.ALL,
                    dataIndex:'id',
                    itemId:'all',
                    width:30,
                    scope:this,
                    renderer:function(value, metaData, record, rowIndex, colIndex, store){
                        var allChecked = this.checkPermissionsCol(record);
                        if(allChecked)
                            return '<img src="'+app.wwwRoot+'i/system/checked.gif">';
                        else
                            return '<img src="'+app.wwwRoot+'i/system/unchecked.gif">';
                    }
                },{
                    text:appLang.VIEW,
                    dataIndex:'view',
                    align:'center',
                    renderer:app.checkboxRenderer,
                    xtype:'checkcolumn'
                },{
                    text:appLang.CREATE,
                    dataIndex:'create',
                    align:'center',
                    renderer:app.checkboxRenderer,
                    xtype:'checkcolumn'
                },{
                    text:appLang.EDIT,
                    dataIndex:'edit',
                    align:'center',
                    renderer:app.checkboxRenderer,
                    xtype:'checkcolumn'
                },{
                    text:appLang.DELETE,
                    dataIndex:'delete',
                    align:'center',
                    renderer:app.checkboxRenderer,
                    xtype:'checkcolumn'
                },{
                    text:appLang.TO_PUBLISH,
                    dataIndex:'publish',
                    id:'publish',
                    align:'center',
                    xtype:'checkcolumn',
                    renderer:function(value, metaData, record, rowIndex, colIndex, store){
                        if(record.get('rc'))
                            return app.checkboxRenderer(value, metaData, record, rowIndex, colIndex, store);
                        else
                            return '-';
                    }
                }],
            plugins: [cellEditing]

        });
        this.dataGrid.on('cellclick',function(grid, cell, columnIndex, record , node , rowIndex , evt){
            var column = grid.getHeaderCt().getHeaderAtIndex(columnIndex).itemId;

            switch(column)
            {
                case 'all':
                    var allChecked = this.checkPermissionsCol(record);

                    var check = false;
                    if (!allChecked){
                        check = true;
                    }
                    record.set('view' , check);
                    record.set('edit' , check);
                    record.set('delete' , check);
                    record.set('create' , check);
                    if(record.get('rc')){
                        record.set('publish' , check);
                    }
                    return false;
                    break;

                case 'publish':
                    if(!record.get('rc'))
                        return false;
                    break;
            }
        },this);

        this.items = [this.dataGrid];
        this.callParent(arguments);
    },
    checkPermissionsCol:function(record){
        var toCheck = ['create','view','edit','publish','delete'];
        var allChecked = true;
        Ext.each(toCheck , function(item){
            if(item !='publish'){
                if(!record.get(item)){
                    allChecked = false;
                }
            }
            if(item=='publish' && record.get('rc')){
                if(!record.get(item)){
                    allChecked = false;
                }
            }
        },this);
        return allChecked;
    },
    savePermissions: function(){
        var store = this.dataStore;
        var data = app.collectStoreData(this.dataStore);
        data = Ext.encode(data);
        Ext.Ajax.request({
            url:this.controllerUrl + 'savepermissions',
            method: 'post',
            params:{
                'data':data,
                'user_id':store.proxy.extraParams['user_id'],
                'group_id':store.proxy.extraParams['group_id']
            },
            success: function(response, request) {
                response =  Ext.JSON.decode(response.responseText);
                if(response.success){
                    store.commitChanges();
                }else{
                    Ext.Msg.alert(' ', response.msg);
                }
            },
            failure:app.ajaxFailure
        });
    }
});

Ext.define('app.crud.acl.Groups',{
    extend:'Ext.Panel',
    dataGrid:null,
    dataStore:null,
    permissionsPanel:null,
    controllerUrl:null,
    constructor: function(config) {
        config = Ext.apply({
            modal: true,
            layout: {
                type: 'hbox',
                pack: 'start',
                align: 'stretch'
            }
        }, config || {});

        this.callParent(arguments);
    },
    initComponent:function(){
        this.dataStore = Ext.create('Ext.data.Store', {
            proxy: {
                type: 'ajax',
                url: this.controllerUrl + 'grouplist',
                reader: {
                    type: 'json',
                    rootProperty: 'data',
                    totalProperty: 'count',
                    idProperty: 'id'
                },
                startParam:'pager[start]',
                limitParam:'pager[limit]',
                sortParam:'pager[sot]',
                dirParam:'pager[dir]',
                simpleSortMode: true
            },
            fields: [
                {name:'id' , type:'integer'},
                {name:'title' , type:'string'},
                {name:'system' , type:'boolean'}
            ],
            pageSize: 50,
            remoteSort: true,
            autoLoad: true,
            sorters: [{
                property : 'title',
                direction: 'ASC'
            }]
        });

        this.dataGrid = Ext.create('Ext.grid.Panel',{
            store: this.dataStore,
            viewConfig:{
                stripeRows:true
            },
            loadMask:true,
            columnLines: true,
            scrollable:true,
            frame: false,
            width:300,
            title:appLang.GROUPS,
            columns:[
                {
                    title:appLang.NAME,
                    dataIndex:'title',
                    align:'left',
                    flex:1
                },{
                    header:appLang.SYSTEM,
                    dataIndex:'system',
                    align:'center',
                    width:60,
                    renderer:app.checkboxRenderer
                }
            ]
        });

        this.permissionsPanel =  Ext.create('app.crud.acl.Permissions',{
            flex:1,
            frame:false,
            border:false,
            canEdit:this.canEdit,
            controllerUrl:this.controllerUrl
        });
        if(this.canEdit){
            this.dataGrid.on('cellclick',function(grid, cell, columnIndex, record , node , rowIndex , evt){
                this.permissionsPanel.saveBtn.enable();
                this.permissionsPanel.dataGrid.getSelectionModel().deselectAll();
                this.dataGrid.getSelectionModel().select(rowIndex , false);
                this.groupSelected(rowIndex , record);
            },this);
        }
        this.items=[this.dataGrid,this.permissionsPanel];
        this.callParent(arguments);
    },

    groupSelected: function(rowIndex , record){
        var store = this.permissionsPanel.dataStore;
        this.permissionsPanel.setTitle('"'+record.get('title')+'" ' + appLang.GROUP_PERMISSIONS);
        store.proxy.setExtraParam('group_id' , record.get('id'));
        store.proxy.setExtraParam('user_id' ,  0);
        store.removeAll();
        store.load();
    }
});
