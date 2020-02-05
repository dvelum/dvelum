/**
 *
 * @event select
 *
 */
Ext.define('app.crud.orm.DataViewWindow', {
    extend:'Ext.window.Window',
    objectName:'',
    controllerUrl:'',
    width:900,
    height:600,
    maximizable:true,
    readOnly:false,
    layout:'fit',

    dataGrid:null,
    dataStore:null,
    searchField:null,
    isVc:null,
    primaryKey:'id',

    editorCfg:false,
    selectMode:false,
    closeOnSelect:true,

    modal:true,
    relatedGrids:null,
    shard:0,
    shardField:null,

    initComponent:function(){

        if(this.selectMode){
            this.buttons = [{
                text:appLang.SELECT,
                scope:this,
                handler:this.selectItem
            },{
                text:appLang.CLOSE,
                scope:this,
                handler:this.close
            }];
        }

        this.callParent();

        this.on('show',function(){
            app.checkSize(this);
            this.loadInterface();
        },this);
    },
    loadInterface:function(){
        var me = this;
        me.getEl().mask(appLang.LOADING);
        Ext.Ajax.request({
            url:this.controllerUrl + 'viewconfig',
            method: 'post',
            params:{
                d_object:this.objectName,
                shard:this.shard
            },
            scope:this,
            success: function(response, request) {
                response =  Ext.JSON.decode(response.responseText);
                if(!response.success){
                    Ext.Msg.alert(appLang.MESSAGE , response.msg);
                } else {
                    this.configurate(response.data);
                }
                me.getEl().unmask();
            },
            failure:function(){
                me.getEl().unmask();
                app.ajaxFailure(arguments);
            }
        });
    },
    configurate:function(data){

        this.setTitle(data.title);
        this.shardField = data.shardField;

        this.dataStore =  Ext.create('Ext.data.Store', {
            fields:data.fields,
            remoteSort:true,
            proxy: {
                type: 'ajax',
                url:this.controllerUrl + 'list',
                directionParam:"pager[dir]",
                limitParam:"pager[limit]",
                sortParam:"pager[sort]",
                startParam:"pager[start]",
                extraParams:{
                    d_object:this.objectName,
                    shard:this.shard
                },
                reader: {
                    type:'json',
                    idProperty:"id",
                    rootProperty:"data",
                    totalProperty:"count"
                },
                simpleSortMode: true
            },
            autoLoad: true
        });

        var cols = [];

        if(!this.selectMode && data.canEditObject)
        {
            cols.push({
                xtype:'actioncolumn',
                width:30,
                align:'center',
                items:[
                    {
                        iconCls:'editIcon',
                        scope:this,
                        tooltip:appLang.EDIT,
                        handler:function(grid, rowIndex, colIndex){
                            var rec = grid.getStore().getAt(rowIndex);
                            this.showEdit(rec);
                        }
                    }
                ]
            });
        }

        Ext.each(data.columns , function(item){
            cols.push(item);
        });

        if(!this.selectMode && data.canEditObject)
        {
            cols.push(
                {
                    xtype:'actioncolumn',
                    width:30,
                    align:'center',
                    items:[
                        {
                            iconCls:'deleteIcon',
                            scope:this,
                            tooltip:appLang.DELETE,
                            handler:function(grid, rowIndex, colIndex){
                                var rec = grid.getStore().getAt(rowIndex);
                                var shard = null;
                                if (this.shardField) {
                                    shard = rec.get(me.shardField);
                                }
                                this.deleteItem(rec.get('id') , shard);
                            }
                        }
                    ]
                });
        }

        var tBar = [];

        if(!this.selectMode && !this.readOnly && !this.shard)
        {
            tBar.push({
                text:appLang.ADD_ITEM,
                scope:this,
                handler:function(){this.showEdit(0);}
            });
        }

        this.searchField = Ext.create('SearchPanel',{
            store:this.dataStore,
            isLocal:false,
            fieldNames:data.searchFields
        });
        var me = this;

        this.shardSelector = Ext.create('Ext.form.field.ComboBox',{
            queryMode:'local',
            displayField:'id',
            forceSelection:true,
            valueField:'id',
            store: Ext.create('Ext.data.Store',{
                model:'app.comboStringModel',
                autoLoad:true,
                proxy: {
                    type: 'ajax',
                    url:this.controllerUrl + 'shardList',
                    reader: {
                        type: 'json',
                        rootProperty: 'data',
                        idProperty: 'id'
                    }
                },
                remoteSort:false,
                sorters: [{
                    property : 'id',
                    direction: 'ASC'
                }]
            })
        });
        if(data.selectShard){
            tBar.push(
                '-',
                appLang.SHARD_DATA_EDITOR +':',
                this.shardSelector,
                {
                    xtype:'button',
                    text:appLang.OPEN_EDITOR,
                    iconCls:'gridIcon',
                    handler:function(){
                      this.showShard(this.shardSelector.getValue());
                    },
                    scope:this
                },
            )
        }
        if(data.findBucket){
            tBar.push('-',{
                xtype:'button',
                iconCls:'searchIcon',
                text:appLang.FIND_BUCKET,
                handler:function(){
                    me.showFindBucket(data.findBucket);
                },
                scope:this
            });
        }

        tBar.push('->', this.searchField);

        this.dataGrid = Ext.create('Ext.grid.Panel',{
            columns:cols,
            selModel:Ext.create('Ext.selection.RowModel',{mode:'single'}),
            columnLines:true,
            store:this.dataStore,
            loadMask:true,
            tbar:tBar,
            viewConfig:{
                enableTextSelection: true
            },
            bbar:Ext.create("Ext.PagingToolbar", {
                store: this.dataStore,
                displayInfo: true,
                displayMsg: appLang.DISPLAYING_RECORDS + " {0} - {1} " + appLang.OF + " {2}",
                emptyMsg:appLang.NO_RECORDS_TO_DISPLAY
            })
        });

        if(this.selectMode || !data.canEditObject){
            this.dataGrid.on('celldblclick',function(table, td, cellIndex, record, tr, rowIndex, e, eOpts ){
                this.fireEvent('select',record);
                if(this.closeOnSelect){
                    this.close();
                }
            },this);
        }else{
            this.dataGrid.on('celldblclick',function(table, td, cellIndex, record, tr, rowIndex, e, eOpts ){
                this.showEdit(record);
            },this);
        }
        this.add(this.dataGrid);
    },
    selectItem:function()
    {
        var sm = this.dataGrid.getSelectionModel();
        if(!sm.hasSelection()){
            Ext.Msg.alert(appLang.MESSAGE,appLang.MSG_SELECT_ITEM_FOR_ADDING);
            return;
        }
        this.fireEvent('select',sm.getSelection()[0]);
        if(this.closeOnSelect){
            this.close();
        }
    },
    showEdit:function(record)
    {
        if(!this.editorCfg)
        {
            var me = this;
            me.getEl().mask(appLang.LOADING);
            Ext.Ajax.request({
                url:this.controllerUrl + 'editorConfig',
                method: 'post',
                params:{
                    d_object:this.objectName
                },
                scope:this,
                success: function(response, request) {
                    response =  Ext.JSON.decode(response.responseText);
                    if(!response.success){
                        Ext.Msg.alert(appLang.MESSAGE , response.msg);
                    } else {
                        me.editorCfg = response.data;
                        var shard = null;
                        if(!Ext.isEmpty(me.editorCfg.shard)){

                        }
                        if(!record){
                            this.createEditWindow(null, null);
                        }else {
                            if (this.shardField) {
                                me.createEditWindow(
                                    record.get(me.primaryKey),
                                    record.get(me.shardField)
                                );
                            } else {
                                me.createEditWindow(
                                    record.get(this.primaryKey),
                                    null
                                );
                            }
                        }
                    }
                    me.getEl().unmask();
                },
                failure:function(){
                    me.getEl().unmask();
                    app.ajaxFailure(arguments);
                }
            });
        }else{
            if(!record){
                this.createEditWindow(null, null);
            }else{
                if(this.shardField){
                    this.createEditWindow(record.get(this.primaryKey), record.get(this.shardField));
                }else{
                    this.createEditWindow(record.get(this.primaryKey), null);
                }
            }


        }
    },
    createEditWindow:function(id, shard)
    {
        var win;
        var me = this;
        var related = this.editorCfg.related;
        var fields = Ext.JSON.decode(this.editorCfg.fields);

        this.relatedGrids = [];

        if(!Ext.isEmpty(related)){
            Ext.each(related , function(item){
                var grid = Ext.create('app.relatedGridPanel',{
                    title:item.title,
                    fieldName:item.field,
                    listeners:{
                        addItemCall: {
                            fn:function(){
                                Ext.create('app.crud.orm.DataViewWindow', {
                                    width:600,
                                    height:500,
                                    selectMode:true,
                                    closeOnSelect:false,
                                    objectName:item.object,
                                    controllerUrl:this.controllerUrl,
                                    isVc:this.isVc,
                                    title:item.title,
                                    readOnly:this.editorCfg.readOnly,
                                    primaryKey:this.editorCfg.primaryKey,
                                    listeners: {
                                        scope: this,
                                        select:function(record){
                                            if(record.get('published')!= undefined){
                                                var published = record.get('published');
                                            }else{
                                                var published = 1;
                                            }
                                            me.relatedGrids[item.field].addRecord(app.relatedGridModel.create({
                                                'id':record.get('id'),
                                                'published':published,
                                                'title':record.get(item.titleField),
                                                'deleted':0
                                            }));
                                        }
                                    }
                                }).show();
                            },
                            scope:this
                        }
                    }
                });
                this.relatedGrids[item.field] = grid;
                fields.push(grid);
            },this);
        }

        if(this.isVc){
            win = Ext.create('app.contentWindow',{
                width:800,
                height:800,
                objectName:this.objectName,
                hasPreview:false,
                items:fields,
                dataItemId:id,
                dataItemShard:shard,
                primaryKey:this.primaryKey,
                controllerUrl:this.controllerUrl,
                canEdit:!this.readOnly,
                canDelete:!this.readOnly,
                canPublish:!this.readOnly,
                listeners:{
                    dataSaved:{
                        fn:function(){
                            me.dataStore.load();
                        },
                        scope:me
                    }
                }
            });

        }else{
            win = Ext.create('app.editWindow',{
                width:800,
                height:800,
                dataItemId:id,
                dataItemShard:shard,
                canEdit:!this.readOnly,
                canDelete:!this.readOnly,
                primaryKey:this.primaryKey,
                items:fields,
                objectName:this.objectName,
                controllerUrl:this.controllerUrl,
                listeners:{
                    dataSaved:{
                        fn:function(){
                            me.dataStore.load();
                        },
                        scope:me
                    }
                }
            });
        }

        if(id && !Ext.isEmpty(this.editorCfg.readOnlyAfterCreate)){
            var form = win.getForm().getForm();
            Ext.Array.each(this.editorCfg.readOnlyAfterCreate,function(item){
                var field = form.findField(item);
                if(field && field.setReadOnly){
                    field.setReadOnly(true);
                }
            });
        }

        if(!id && !Ext.isEmpty(this.editorCfg.readOnlyAfterCreate)){
            win.on('dataSaved',function(){
                var form = win.getForm().getForm();
                Ext.Array.each(this.editorCfg.readOnlyAfterCreate,function(item){
                    var field = form.findField(item);
                    if(field && field.setReadOnly){
                        field.setReadOnly(true);
                    }
                });
            },this)
        }
        win.show();
    },
    /**
     * Delete record
     */
    deleteItem : function(itemId, shard){
        if(!Ext.isNumeric(itemId)){
            return false;
        }

        var me = this;
        Ext.Ajax.request({
            url: this.controllerUrl + 'delete',
            waitMsg:appLang.PROCESSING,
            method: 'post',
            params: {
                'id':itemId,
                'd_object':this.objectName,
                'shard':shard
            },
            success: function(response, request) {
                response =  Ext.JSON.decode(response.responseText);
                if(response.success){
                    me.dataStore.load();
                }else{
                    Ext.MessageBox.alert(appLang.MESSAGE,response.msg);
                }
            }
        });
    },
    /**
     * show shard view
     * @param shard
     */
    showShard:function(shard)
    {
        if(Ext.isEmpty(shard)){
            return;
        }
        var win = Ext.create('app.crud.orm.DataViewWindow',{
            objectName:this.objectName,
            title:this.title,
            isVc:this.isVc,
            modal:true,
            readOnly:this.readOnly,
            primaryKey:this.primaryKey,
            shardKey:this.shardField,
            controllerUrl:app.crud.orm.Actions.dataViewController,
            shard:shard
        });
        win.show();
    },
    showFindBucket:function(bucketConfig){
        Ext.create('app.crud.orm.DataViewFindBucketWindow',{
            fieldName:bucketConfig.field,
            objectName:this.objectName,
            controllerUrl:this.controllerUrl,
            listeners:{
                bucket_selected:{
                    fn:function(bucket){
                        this.searchField.setValue(bucket);
                    },
                    scope:this
                }
            }
        }).show();
    },
    destroy:function(){
        if(!Ext.isEmpty(this.relatedGrids)){
            Ext.Object.each(this.relatedGrids , function(key, item){
                if(item && item.destroy){
                    item.destroy();
                }
            });
        }
        this.shardSelector.destroy();
        this.dataGrid.destroy();
        this.dataStore.destroy();
        this.searchField.destroy();
        this.callParent(arguments);
    }
});
Ext.define('app.crud.orm.DataViewFindBucketWindow',{
    extend:'Ext.window.Window',
    width:300,
    height:150,
    bodyCls:'formBody',
    padding:10,
    layout:'fit',
    title:appLang.FIND_BUCKET,
    resizable:false,
    modal:true,
    controllerUrl:'',

    initComponent:function() {
       var me = this;

       this.bucketField = Ext.create('Ext.form.field.Text',{
           border:false,
           cls:'formBody',
           readOnly:true,
           name:'bucket',
           fieldLabel:appLang.BUCKET,
       });

       this.items = [
           {
               xtype:'form',
               bodyCls:'formBody',
               padding:10,
               border:false,
               bodyBorder:false,
               fieldDefaults:{
                   anchor:'100%',
                   labelAlign:'right',
                   labelWidth:100
               },
               items:[
                   {
                       xtype:'textfield',
                       fieldLabel:this.fieldName,
                       enableKeyEvents:true,
                       listeners:{
                           change:{
                               fn:function(field, newValue) {
                                   me.applyButton.disable();
                                   me.bucketField.reset();
                                   me.findBucket(newValue);
                               },
                               scope:me,
                               buffer:400
                           }
                       }
                   },
                   this.bucketField
               ]
           }
       ];
       this.applyButton = Ext.create('Ext.Button',{
           text:appLang.SEARCH,
           disabled:true,
           handler:function(){
               this.applyBucket();
           },
           scope:this
       });
       this.buttons=[
           this.applyButton,
           {
               text:appLang.CLOSE,
               handler:this.close,
               scope:this
           }
       ];
       this.callParent();
    },
    findBucket:function(fieldValue){

        if(!fieldValue.length){
            return;
        }
        var me = this;
        Ext.Ajax.request({
            url:this.controllerUrl + 'findBucket',
            method: 'post',
            params:{
                d_object:this.objectName,
                value:fieldValue
            },
            scope:this,
            success: function(response, request) {
                response =  Ext.JSON.decode(response.responseText);
                if(!response.success){
                    Ext.Msg.alert(appLang.MESSAGE , response.msg);
                } else {
                    if(response.data.bucket){
                        this.bucketField.setValue(response.data.bucket);
                        this.applyButton.enable();
                    }else{
                        this.bucketField.reset();
                    }

                }
                me.getEl().unmask();
            },
            failure:function(){
                me.getEl().unmask();
                app.ajaxFailure(arguments);
            }
        });
    },
    destroy:function(){
        this.applyButton.destroy();
        this.bucketField.destroy();

        this.callParent(arguments);
    },
    applyBucket:function(){
        this.fireEvent('bucket_selected', this.bucketField.getValue());
        this.close();
    }
});