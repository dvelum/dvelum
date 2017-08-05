/**
 * DVelum project http://code.google.com/p/phpveil/ , dvelum.net
 * @author Andrew Zamotaev 2012
 * @author improved and bugfixed by Kirill A Egorov 2012
 *
 * @event dictionarySaved
 *
 */
Ext.define('app.crud.orm.DictionaryRecordModel',{
    extend: 'Ext.data.Model',
    fields: [
        {name:'id' , type:'string'},
        {name:'key' ,  type:'string'},
        {name:'value' ,  type:'string'}
    ],
    idProperty:'id'
});

Ext.define('app.crud.orm.AddDictionaryWindow', {
    extend:'Ext.window.Window',

    dataForm:null,
    dictionryId:null,
    valueField:'',

    constructor: function(config) {
        config = Ext.apply({
            layout:'fit',
            modal:true,
            bodyCls:'formBody',
            width: app.checkWidth(300),
            height:app.checkHeight(120),
            closeAction: 'destroy',
            resizable:false,
            title:appLang.DICTIONARY_NAME
        }, config || {});
        this.callParent(arguments);
    },
    initComponent:function(){
        this.dataForm = Ext.create('Ext.form.Panel',{
            border:false,
            bodyPadding: 15,
            bodyCls:'formBody',
            layout: 'anchor',
            defaults: {
                anchor: '100%'
            },
            items:[{
                xtype:'hidden',
                name:'id',
                value:this.dictionryId
            },{
                xtype:'textfield',
                fieldLabel:appLang.NAME,
                name:'name',
                allowBlank:false,
                vtype:"alpha",
                labelWidth:50,
                value:this.valueField
            }]
        });

        this.buttons = [{
            text:appLang.SAVE,
            scope:this,
            handler:this.saveNewDictionary
        }];

        this.items = [this.dataForm];

        this.callParent(arguments);
    },
    saveNewDictionary:function(){
        this.dataForm.getForm().submit({
            clientValidation: true,
            waitMsg:appLang.SAVING,
            method:'post',
            scope:this,
            url:this.controllerUrl + 'update',
            success: function(form, action) {
                if(!action.result.success){
                    Ext.Msg.alert(appLang.MESSAGE, action.result.msg);
                } else{
                    this.fireEvent('dictionarySaved', form.findField('name').getValue());
                    this.close();
                }
            },
            failure:app.formFailure
        });
    }
});

Ext.define('app.crud.orm.DictionaryWindow', {
    extend:'Ext.window.Window',

    curDictionary:null,
    canEdit:false,
    canDelete:false,

    addRecordButton:null,
    saveRecordsButton:null,
    renameDictionaryButton:null,

    dictionaryGrid:null,
    recordsGrid:null,

    dictionaryStore:null,
    recordsStore:null,

    cellEditingRecords:null,

    controllerUrl:'',

    constructor: function(config) {
        config = Ext.apply({
            layout:'border',
            modal:true,
            width: app.checkWidth(700),
            height:app.checkHeight(500),
            closeAction: 'destroy',
            maximizable:true,
            title:appLang.DICTIONARIES
        }, config || {});
        this.callParent(arguments);
    },

    initComponent:function(){

        this.dictionaryStore = Ext.create('Ext.data.Store',{
            model: 'app.comboStringModel',
            proxy: {
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
            }],
            listeners:{
                scope:this,
                load:this.selectCurentItem
            }
        });

        var dictionaryGridColumns = [{
            text: appLang.NAME,
            flex: 1,
            dataIndex: 'title'
        }];

        if(this.canDelete){
            dictionaryGridColumns.push({
                xtype:'actioncolumn',
                width:20,
                align:'center',
                itemId:'removeAction',
                items: [{
                    iconCls: 'deleteIcon',
                    width:20,
                    tooltip: appLang.DELETE,
                    scope:this,
                    handler: this.deleteDictionary
                }]
            });
        }

        this.dictionaryGrid = Ext.create('Ext.grid.Panel',{
            region:'center',
            store:this.dictionaryStore,
            layout:'fit',
            columnLines:true,
            columns:dictionaryGridColumns,
            viewConfig:{
                enableTextSelection: true
            }
        });

        this.renameDictionaryButton = Ext.create('Ext.button.Button',{
            text:appLang.RENAME,
            scope:this,
            handler:this.updateDictionary,
            disabled:true
        });

        if(this.canEdit){
            this.dictionaryGrid.addDocked({
                xtype: 'toolbar',
                dock: 'top',
                items: [{
                    text:appLang.ADD,
                    scope:this,
                    handler:this.addDictionary
                },this.renameDictionaryButton]
            });
        }

        this.dictionaryGrid.on('cellclick',this.onDictionaryClick,this);

        this.cellEditingRecords = Ext.create('Ext.grid.plugin.CellEditing', {
            clicksToEdit: 1
        });

        this.recordsStore = Ext.create('Ext.data.Store',{
            model:'app.crud.orm.DictionaryRecordModel',
            proxy: {
                type: 'ajax',
                url:this.controllerUrl + 'records',
                reader: {
                    type: 'json',
                    rootProperty: 'data'
                }
            },
            autoLoad: false,
            sorters: [{
                property : 'key',
                direction: 'ASC'
            }]
        });

        this.addRecordButton = Ext.create('Ext.button.Button',{
            text:appLang.ADD,
            scope:this,
            handler:this.addDictionaryRec,
            disabled:true
        });
        this.saveRecordsButton = Ext.create('Ext.button.Button',{
            text:appLang.SAVE,
            scope:this,
            handler:this.saveDictionaryRec,
            disabled:true
        });

        var recordsGridColumns = [{
            text: appLang.KEY,
            flex: 1,
            dataIndex: 'key',
            editor: {
                allowBlank: false,
                vtype:"alphanum"
            }
        },{
            text:appLang.VALUE,
            dataIndex: 'value',
            editor: {
                allowBlank: false
            }
        }];

        if(this.canDelete){
            recordsGridColumns.push({
                xtype:'actioncolumn',
                width:20,
                align:'center',
                itemId:'removeAction',
                items: [{
                    iconCls: 'deleteIcon',
                    width:20,
                    tooltip:appLang.DELETE,
                    scope:this,
                    handler:this.deleteDictionaryRec
                }]
            });
        }

        this.recordsGrid = Ext.create('Ext.grid.Panel',{
            region:'east',
            width:350,
            columnLines:true,
            split:true,
            store:this.recordsStore,
            plugins:[this.cellEditingRecords],
            layout:'fit',
            columns:recordsGridColumns,
            viewConfig:{
                enableTextSelection: true
            }
        });

        if(this.canEdit){
            this.recordsGrid.addDocked([{
                xtype: 'toolbar',
                dock: 'top',
                items: [this.addRecordButton]
            },{
                xtype: 'toolbar',
                dock: 'bottom',
                ui: 'footer',
                defaults: {minWidth: 75},
                items: ['->',this.saveRecordsButton]
            }]);
        }

        this.items = [this.dictionaryGrid , this.recordsGrid];

        this.callParent(arguments);

        if(this.curDictionary){
            this.loadRecordsStore();
        }
    },
    selectCurentItem:function(){
        if(this.curDictionary){
            var toSelect = null;
            this.dictionaryStore.each(function(record){
                if(record.get('title') == this.curDictionary){
                    toSelect = record;
                    return false;
                }
            },this);

            if(toSelect != null){
                this.dictionaryGrid.getSelectionModel().select(toSelect);
            }
        }
    },
    setDisabledRenameButton:function(bool){
        this.renameDictionaryButton.setDisabled(bool);
    },
    onDictionaryClick:function(grid, cell, columnIndex, record , node , rowIndex , evt){
        var cellId = grid.getHeaderAtIndex(columnIndex).itemId;
        if(cellId == 'removeAction'){
            return;
        }
        this.setDisabledRenameButton(false);
        this.setCurDictionary(record.get('id'), true);
    },
    setCurDictionary:function(name, load){
        this.curDictionary = name;
        if(load){
            this.loadRecordsStore();
        }
    },
    loadRecordsStore:function(){
        this.unsetCurDictionary();
        this.recordsStore.proxy.setExtraParam('dictionary' , this.curDictionary);
        this.recordsStore.load({
            scope   : this,
            callback: function(records, operation, success) {
                if(success){
                    this.addRecordButton.enable();
                    this.saveRecordsButton.enable();
                }
            }
        });
    },
    unsetCurDictionary:function(){
        this.recordsStore.removeAll();
        this.addRecordButton.disable();
        this.saveRecordsButton.disable();
    },
    addDictionary:function(grid, rowIndex, colIndex){
        var win = Ext.create('app.crud.orm.AddDictionaryWindow',{
            controllerUrl:this.controllerUrl
        });
        win.on('dictionarySaved',function(){
            this.dictionaryStore.load();
            this.setDisabledRenameButton(true);
        },this);
        win.show();
    },
    addDictionaryRec:function(){
        var r = Ext.create('app.crud.orm.DictionaryRecordModel');
        var pos  = this.recordsStore.getCount();
        pos++;
        r.set({key:'new',value:'empty','id':pos},{dirty: true});

        this.recordsStore.insert(pos, r);
        var index = this.recordsStore.findExact('id',pos);
        if(index >=0){
            this.cellEditingRecords.startEdit({row:index, column: 0});
        }
    },
    updateDictionary:function(){
        var record = this.dictionaryGrid.getView().getSelectionModel().getSelection()[0];
        if (record) {
            var win = Ext.create('app.crud.orm.AddDictionaryWindow',{
                controllerUrl:this.controllerUrl,
                dictionryId:record.get('id'),
                valueField:record.get('title')
            });
            win.on('dictionarySaved',function(newTitle){
                this.setCurDictionary(newTitle, false);
                this.dictionaryStore.load();
                this.setDisabledRenameButton(true);
            },this);
            win.show();
        } else {
            Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_SELECT_RECORD);
        }
    },
    saveDictionaryRec:function()
    {
        var data = app.collectStoreData(this.recordsStore, 'id');
        if(data.length == 0){
            Ext.Msg.alert(appLang.MESSAGE , appLang.NTD);
            return;
        }
        Ext.Ajax.request({
            url:this.controllerUrl + 'updaterecords',
            method: 'post',
            params:{
                data:Ext.JSON.encode(data),
                dictionary:this.curDictionary
            },
            scope:this,
            success: function(response, request) {
                response =  Ext.JSON.decode(response.responseText);
                if(!response.success){
                    Ext.Msg.alert(appLang.MESSAGE , response.msg);
                } else {
                    this.loadRecordsStore();
                }
            },
            failure:app.ajaxFailure
        });
    },
    deleteDictionary:function(grid, rowIndex, colIndex, item, eventObj){
        var name = grid.getStore().getAt(rowIndex).get('id');
        Ext.Msg.confirm(appLang.CONFIRM, appLang.MSG_CONFIRM_DELETE_DICTIONARY +' "'+name+'"?', function(btn){
            if(btn != 'yes'){
                return;
            }
            Ext.Ajax.request({
                url:this.controllerUrl + 'remove',
                method: 'post',
                scope:this,
                params:{
                    name:name
                },
                success: function(response, request) {
                    response =  Ext.JSON.decode(response.responseText);
                    if(response.success){
                        grid.getStore().removeAt(rowIndex);
                        if(name == this.curDictionary){
                            this.unsetCurDictionary();
                            this.setDisabledRenameButton(true);
                        }
                    }else{
                        Ext.Msg.alert(appLang.MESSAGE , response.msg);
                    }
                },
                failure:app.ajaxFailure
            });
        },this);
    },
    deleteDictionaryRec:function(grid, rowIndex, colIndex, item, eventObj){
        var record = grid.getStore().getAt(rowIndex);

        if(record.dirty){
            grid.getStore().remove(record);
            return;
        }

        var name = record.get('id');
        Ext.Msg.confirm(appLang.CONFIRM, appLang.MSG_CONFIRM_DELETE +' "'+name+'"?', function(btn){
            if(btn != 'yes'){
                return;
            }
            Ext.Ajax.request({
                url:this.controllerUrl + 'removerecords',
                method: 'post',
                scope:this,
                params:{
                    name:name,
                    dictionary:this.curDictionary
                },
                success: function(response, request) {
                    response =  Ext.JSON.decode(response.responseText);
                    if(response.success){
                        this.recordsStore.removeAt(rowIndex);
                    }else{
                        Ext.Msg.alert(appLang.MESSAGE , response.msg);
                    }
                },
                failure:app.ajaxFailure
            });
        },this);
    }
});