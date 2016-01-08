Ext.ns('app.crud.mediaconfig');

Ext.define('app.crud.mediaconfig.Model', {
    extend: 'Ext.data.Model',
    fields: [
        {name:'code' , type:'string'},
        {name:'width' , type:'integer'},
        {name:'height' , type:'integer'},
        {name:'resize' , type:'string'}
    ],
    idProperty:'code'
});

Ext.define('app.crud.mediaconfig.Main',{
    extend:'Ext.Panel',
    dataStore:null,
    dataGrid:null,
    searchField:null,
    saveButton:null,
    canEdit:false,
    canDelete:false,
    bodyCls:'formBody',
    controllerUrl:null,

    constructor: function(config) {
        config = Ext.apply({
            layout:'fit'
        }, config || {});
        this.callParent(arguments);
    },

    initComponent: function(){

        this.saveButton = Ext.create('Ext.Button',{
            hidden:!this.canEdit,
            text:appLang.SAVE,
            iconCls:'saveIcon',
            scope:this,
            handler:this.saveAction
        });

        this.addButton = Ext.create('Ext.Button',{
            hidden:!this.canEdit,
            text:appLang.ADD,
            scope:this,
            handler:this.addAction
        });

        this.recropButton = Ext.create('Ext.Button',{
            hidden:!this.canEdit,
            text:appLang.RECROP,
            scope:this,
            handler:this.recropAction
        });

        this.cellEditing = Ext.create('Ext.grid.plugin.CellEditing', {clicksToEdit: 1});
        this.dataStore = Ext.create('Ext.data.Store' , {
            model:'app.crud.mediaconfig.Model',
            autoLoad:true,
            autoSave:false,
            proxy:{
                type: 'ajax',
                api: {
                    read    : this.controllerUrl + 'list',
                    update  : this.controllerUrl + 'update',
                    create	: this.controllerUrl + 'update',
                    destroy : this.controllerUrl + 'delete'
                },
                reader: {
                    type: 'json',
                    rootProperty: 'data'
                },
                actionMethods : {
                    create : 'POST',
                    read   : 'POST',
                    update : 'POST',
                    destroy: 'POST'
                },
                simpleSortMode: true,
                writer:{
                    writeAllFields:true,
                    encode: true,
                    listful:true,
                    rootProperty:'data'
                }
            },
            listeners:{
                exception:app.storeException
            },
            sorters: [{
                property : 'width',
                direction: 'ASC'
            }]
        });


        var columns = [
            {
                text: appLang.CODE,
                dataIndex: 'code',
                width:100,
                editor:{
                    xtype:'textfield',
                    allowBlank:false,
                    vtype:"alpha"
                },
                editable:this.canEdit
            },{
                text:appLang.WIDTH,
                dataIndex:'width',
                align:'left',
                width:80,
                editor:{
                    xtype:'numberfield'
                },
                editable:this.canEdit
            },{
                text:appLang.HEIGHT,
                dataIndex: 'height',
                width:80,
                editor:{
                    xtype:'numberfield'
                },
                editable:this.canEdit
            },{
                text:appLang.RESIZE,
                dataIndex: 'resize',
                width:160,
                align:'center',
                editor:{
                    xtype:'combo',
                    queryMode:'local',
                    displayField:'title',
                    valueField:'id',
                    store:Ext.create('Ext.data.Store',{
                        model:'app.comboStringModel',
                        remoteSort:false,
                        proxy: {
                            type: 'ajax',
                            simpleSortMode: true
                        },
                        data:[
                            {id:'crop' , title: appLang.CROP},
                            {id:'resize' , title: appLang.RESIZE},
                            {id:'resize_fit' , title: appLang.RESIZE_FIT}
                        ]
                    })
                },
                editable:this.canEdit,
                renderer:function(value)
                {
                    switch(value)
                    {
                        case 'crop': return appLang.CROP;
                            break;
                        case 'resize': return appLang.RESIZE;
                            break;
                        case 'resize_fit': return appLang.RESIZE_FIT;
                            break;
                    }
                    return '';
                }
            }
        ];

        if(this.canDelete){
            columns.push({
                xtype:'actioncolumn',
                align:'center',
                width:30,
                items:[{
                    iconCls:'deleteIcon',
                    tooltip:appLang.DELETE_ITEM,
                    width:30,
                    handler:function(grid, rowIndex, colIndex){
                        var rec = grid.getStore().getAt(rowIndex);
                        grid.getStore().remove(rec);
                    },
                    scope:this
                }]
            });
        }


        this.dataGrid = Ext.create('Ext.grid.Panel',{
            store: this.dataStore,
            viewConfig:{
                stripeRows:true
            },
            frame: false,
            loadMask:true,
            columnLines: true,
            scrollable:true,
            selModel: {
                selType: 'cellmodel'
            },
            columns: columns,
            plugins: [this.cellEditing]
        });
        if(this.canEdit){
            this.tbar = [this.addButton,'-',this.recropButton,'-',this.saveButton];
        }
        this.items = [this.dataGrid];
        this.callParent(arguments);
    },
    saveAction:function(){
        this.dataStore.save();
    },
    addAction:function(){
        var rec =  Ext.create('app.crud.mediaconfig.Model', {
            code:'size_' + this.dataStore.getCount(),
            width:100,
            height:100,
            resize:'crop'
        });

        var edit = this.cellEditing;
        edit.cancelEdit();

        this.dataStore.insert(0, rec);

        edit.startEditByPosition({
            row: this.dataStore.indexOf(rec),
            column: 0
        });
    },
    recropAction:function(){
        var imageSizes = {};

        this.dataStore.each(function(record){
            imageSizes[record.get('code')] = [record.get('width') , record.get('height')];
        },this);

        var win = Ext.create('app.crud.mediaconfig.CropWindow',{
            title:appLang.RECROP,
            sizeList:imageSizes,
            controllerUrl:this.controllerUrl
        });
        win.show();
    }
});

/**
 * Edit window for ORM object Index
 */
Ext.define('app.crud.mediaconfig.CropWindow', {

    extend: 'Ext.window.Window',
    dataForm:null,
    sizeList:null,
    controllerUrl:null,

    constructor: function(config) {
        config = Ext.apply({
            modal: true,
            layout:'fit',
            width: app.checkWidth(350),
            height:app.checkHeight(300),
            closeAction: 'destroy',
            maximizable:true
        }, config || {});

        this.callParent(arguments);
    },

    /**
     * @todo fix columns menu
     */
    initComponent:function(){
        var groupItems = [];

        Ext.Object.each(this.sizeList, function(key, value) {
            groupItems.push({name:"size[]" , boxLabel:key +' ('+value[0]+'x'+value[1]+')' , inputValue:key });
        });

        this.dataForm = Ext.create('Ext.form.Panel',{
            bodyPadding:5,
            scrollable:true,
            bodyCls:'formBody',
            items:[
                {
                    xtype:'checkbox',
                    name:'notcroped',
                    boxLabel:appLang.MSG_RECROP_ONLY_AUTOCROPED,
                    checked:true
                },{
                    xtype:'checkboxgroup',
                    columns:1,
                    width:250,
                    items:groupItems
                }
            ]
        });

        this.buttons = [
            {
                text:appLang.START,
                scope:this,
                handler:this.cropAction
            },{
                text:appLang.CANCEL,
                scope:this,
                handler:this.close
            }
        ];

        this.items = [this.dataForm];
        this.callParent(arguments);
    },
    cropAction:function(){
        this.dataForm.getForm().submit({
            clientValidation: true,
            waitMsg:appLang.SAVING,
            method:'post',
            url:this.controllerUrl + 'startcrop',
            success: function(form, action) {
                if(!action.result.success){
                    Ext.Msg.alert(appLang.MESSAGE, action.result.msg);
                } else{

                }
            },
            failure: app.formFailure
        });

        if(!app.taskWindow){
            setTimeout(function(){
                var url = app.createUrl([app.admin , 'tasks']);
                app.taskWindow = window.open(url,'taskWindow','toolbar=0,status=0,menubar=0');
            },2000);
        }else{
            setTimeout(function(){
                app.taskWindow.focus();
            },2000);
        }
    }
});

