/**
 * @event dataChanged
 */
Ext.define('app.report.ConfigForm',{
    extend:'Ext.form.Panel',
    title:'',
    initComponent:function(){
        this.callParent();
    }
});
/**
 * @event dataChanged
 */
Ext.define('app.report.ConfigWindow',{
    extend:'Ext.Window',
    modal:true,
    layout:'border',
    maximizable :true,
    controllerUrl:'',

    /*
     * Panels
     */
    reportBuilder:null,
    reportQuery:null,
    reportPanel:null,

    /*
     * Buttons
     */
    buttonBase:null,
    buttonSub:null,
    buttonDeleteBase:null,
    buttonSqlReload:null,

    dataItems:null,
    dataTitle:'',

    title:appLang.REPORT_CONFIG,

    initComponent:function()
    {
        this.buttonSqlReload = Ext.create('Ext.Button',{
            iconCls:'refreshIcon',
            tooltip:appLang.RELOAD_SQL,
            handler:this.loadSql,
            scope:this
        });

        this.reportQuery = Ext.create('designer.sqlEditor',{
            title:appLang.QUERY,
            region:'north',
            height:250,
            split:true,
            bodyPadding:10,
            layout:'fit',
            scrollable:true,
            collapsible:true,
            tbar:[this.buttonSqlReload]
        });

        this.buttonBase = Ext.create('Ext.Button',{
            text:appLang.BASE_OBJECT,
            handler:this.addBaseObj,
            hidden:true,
            scope:this
        });

        this.buttonSub = Ext.create('Ext.Button',{
            text:appLang.SUB_OBJECT,
            hidden:true,
            handler:this.addSubObject,
            scope:this
        });

        this.buttonDeleteBase = Ext.create('Ext.Button',{
            tooltip:appLang.CLEAR,
            hidden:true,
            handler:this.clearReport,
            scope:this,
            iconCls:'deleteIcon'
        });

        this.reportBuilder = Ext.create('app.report.ConfigForm',{
            region:'center',
            split:true,
            controllerUrl:this.controllerUrl,
            bodyPadding:3,
            title:this.dataTitle,
            bodyCls :'formBody',
            layout:'auto',
            scrollable:true,
            border:true,
            tbar:[
                 this.buttonDeleteBase,
                 this.buttonBase,
                 '-',
                 this.buttonSub
            ],
            listeners:{
                'dataChanged':{
                    fn:function(){
                        this.loadSql();
                        this.fireEvent('dataChanged');
                    },
                    scope:this
                }
            }
        });

        this.filterPanel = Ext.create('app.report.filter.Main',{
            region:'east',
            layout:'fit',
            collapsible:true,
            controllerUrl:this.controllerUrl,
            minWidth:250,
            width:320,
            split:true,
            listeners:{
                'dataChanged':{
                    fn:function(){
                        this.loadSql();
                        this.fireEvent('dataChanged');
                    },
                    scope:this
                }
            }
        });

        this.items = [
            this.reportQuery,
            this.filterPanel,
            this.reportBuilder
        ];

        this.callParent();

        this.on('show',this.resetInterface,this);

    },
    resetInterface:function()
    {
        this.reportBuilder.removeAll();

        if(!Ext.isEmpty(this.dataItems))
        {
            this.reportBuilder.add(this.dataItems);
            this.reportBuilder.setTitle(this.dataTitle);
            this.buttonBase.hide();
            this.buttonSub.show();
            this.buttonDeleteBase.show();
            this.loadSql();
        }else{
            this.buttonBase.show();
            this.buttonSub.hide();
            this.buttonDeleteBase.hide();
        }
    },
    /**
     * Load SQL Query
     */
    loadSql:function(){
        Ext.Ajax.request({
            url: this.controllerUrl + "loadsql",
            method: 'post',
            scope:this,
            success: function(response, request) {
                response =  Ext.JSON.decode(response.responseText);
                if(response.success){
                    this.reportQuery.setValue(response.data);
                }else{
                    return;
                }
            },
            failure:function() {
                Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
            }
        });
    },
    /**
     * Add base object for report
     */
    addBaseObj:function(){
        var win = Ext.create('app.report.config.addMainWindow',{
            controllerUrl: this.controllerUrl,
            listeners:{
                objectAdded:{
                    fn:this.reloadConfigs,
                    scope:this
                }
            }
        }).show();
    },
    /**
     * Add sub object selection
     */
    addSubObject:function(){
        var win = Ext.create('app.report.config.addSubWindow',{
            controllerUrl: this.controllerUrl,
            listeners:{
                objectAdded:{
                    fn:this.reloadConfigs,
                    scope:this
                }
            }
        }).show();
    },
    reloadConfigs:function(){
        this.getEl().mask(appLang.LOADING);
        Ext.Ajax.request({
            url: this.controllerUrl + "checkloaded",
            method: 'post',
            scope:this,
            success: function(response, request) {
                response =  Ext.JSON.decode(response.responseText);

                if(!response.success){
                    this.getEl().unmask();
                    return;
                }

                if(!Ext.isEmpty(response.data.items)){
                    this.dataItems = response.data.items;
                }else{
                    this.dataItems = [];
                    this.dataTitle = response.data.partconfig.title + ' ('+response.data.partconfig.object+')';
                }

                this.resetInterface();
                this.fireEvent('dataChanged');
                this.getEl().unmask();
            },
            failure:function() {
                this.getEl().unmask();
                Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
            }
        });
    },
    /**
     * Clear report
     */
    clearReport:function(){
        var me = this;
        Ext.Ajax.request({
            url: this.controllerUrl + "clear",
            method: 'post',
            scope:this,
            success: function(response, request) {
                response =  Ext.JSON.decode(response.responseText);
                if(response.success){
                    this.dataItems = [];
                    this.dataTitle = '';
                    this.resetInterface();
                    this.fireEvent('dataChanged');
                }else{
                    Ext.Msg.alert(appLang.MESSAGE,response.msg);
                }
            },
            failure:function() {
                Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
            }
        });
    }
});

/**
 *
 * @event objectAdded
 *
 */
Ext.define('app.report.config.addMainWindow',{
    extend:'Ext.Window',
    width:300,
    height:100,
    dataForm:null,
    closeAction:'desyroy',
    title:appLang.BASE_OBJECT,
    layout:'fit',
    resizable:false,
    modal:true,
    controllerUrl: '',
    initComponent:function(){
        this.dataForm = Ext.create('Ext.form.Panel',{
            bodyPadding:3,
            bodyCls:'formBody',
            border:false,
            fieldDefaults:{
                labelWidth:60,
                anchor:'100%'
            },
            items:[
                Ext.create('Ext.form.field.ComboBox',{
                    displayField:'title',
                    valueField:'id',
                    queryMode:'local',
                    value:'',
                    allowBlank:false,
                    forceSelection:true,
                    fieldLabel:appLang.OBJECT,
                    name:'object',
                    store:Ext.create('Ext.data.Store', {
                        model:'app.comboStringModel',
                        proxy: {
                            type: 'ajax',
                            url: this.controllerUrl + 'objects',
                            reader: {
                                type: 'json',
                                rootProperty: 'data',
                                idProperty: 'id'
                            },
                            simpleSortMode: true
                        },
                        autoLoad:true
                    })
                })
            ]
        });
        this.items = [this.dataForm];
        this.buttons = [{
            text:appLang.ADD,
            scope:this,
            handler:this.formSubmit
        },{
            text:appLang.CANCEL,
            scope:this,
            handler:this.close
        }];
        this.callParent(arguments);
    },
    formSubmit:function(){
        var me = this;
        this.dataForm.getForm().submit({
            clientValidation: true,
            waitTitle:appLang.SAVING,
            method:'post',
            url:this.controllerUrl +  'addbase',
            success: function(form, action)
            {
                if(!action.result.success){
                    Ext.Msg.alert(appLang.MESSAGE, action.result.msg);
                    return;
                }
                me.fireEvent('objectAdded');
                me.close();
            },
            failure: app.formFailure
        });
    }
});

/**
 * @event objectAdded
 */
Ext.define('app.report.config.addSubWindow',{
    extend:'Ext.Window',
    width:400,
    height:200,
    dataForm:null,
    closeAction:'desyroy',
    title:appLang.SUB_OBJECT,
    layout:'fit',
    resizable:false,
    modal:true,
    objectName:0,
    controllerUrl:'',

    fieldListConfig: {
        getInnerTpl: function() {
            return '<tpl for="."><b>{id}</b> - {title}</tpl>';
        }
    },

    initComponent:function()
    {
        this.dataForm = Ext.create('Ext.form.Panel',{
            bodyPadding:3,
            bodyCls:'formBody',
            border:false,
            fieldDefaults:{
                labelWidth:150,
                anchor:'100%',
                labelAlign:'right'
            },
            items:[{
                xtype:'combo',
                displayField:'title',
                valueField:'id',
                queryMode:'local',
                value:'',
                allowBlank:false,
                forceSelection:true,
                fieldLabel:appLang.PARENT_FIELD,
                name:'basefield',
                listConfig: this.fieldListConfig,
                store:Ext.create('Ext.data.Store', {
                    model:'app.comboStringModel',
                    sorters:[{
                        property : 'title',
                        direction: 'ASC'
                    }],
                    proxy: {
                        type: 'ajax',
                        url: this.controllerUrl + 'objectfields',
                        reader: {
                            type: 'json',
                            rootProperty: 'data',
                            idProperty: 'id'
                        },
                        extraParams :{
                            object:this.objectName
                        },
                        simpleSortMode: true
                    },
                    autoLoad:true
                })
            },{
                xtype:'combo',
                displayField:'title',
                valueField:'id',
                queryMode:'local',
                value:'',
                allowBlank:false,
                forceSelection:true,
                fieldLabel:appLang.RELATED_OBJECT,
                name:'subobject',
                store:Ext.create('Ext.data.Store', {
                    model:'app.comboStringModel',
                    sorters:[{
                        property : 'title',
                        direction: 'ASC'
                    }],
                    proxy: {
                        type: 'ajax',
                        url: this.controllerUrl + 'objects',
                        reader: {
                            type: 'json',
                            rootProperty: 'data',
                            idProperty: 'id'
                        },
                        simpleSortMode: true
                    },
                    autoLoad:true
                }),
                listeners:{
                    select:{
                        fn:function(cmp){
                            var subField =  this.dataForm.getForm().findField('subfield');
                            subField.getStore().proxy.setExtraParam('object' , cmp.getValue());
                            subField.getStore().load();
                        },
                        scope:this
                    }
                }
            },{
                xtype:'combo',
                displayField:'title',
                valueField:'id',
                queryMode:'local',
                value:'',
                allowBlank:false,
                forceSelection:true,
                fieldLabel:appLang.LINK_FIELD,
                name:'subfield',
                listConfig: this.fieldListConfig,
                store:Ext.create('Ext.data.Store', {
                    model:'app.comboStringModel',
                    sorters:[{
                        property : 'title',
                        direction: 'ASC'
                    }],
                    proxy: {
                        type: 'ajax',
                        url: this.controllerUrl + 'objectfields',
                        reader: {
                            type: 'json',
                            rootProperty: 'data',
                            idProperty: 'id'
                        },
                        extraParams :{
                            object:this.objectName
                        },
                        simpleSortMode: true
                    },
                    autoLoad:false
                })
            },{
                xtype:'combo',
                displayField:'title',
                valueField:'id',
                queryMode:'local',
                value:1,
                forceSelection:true,
                name:'join',
                fieldLabel:appLang.JOIN_TYPE,
                    store:Ext.create('Ext.data.Store', {
                        model:'app.comboModel',
                        data:[
                            {id:1, title:"Left"},
                            {id:2 ,title:"Right"},
                            {id:3 ,title:"Inner"}
                        ]
                    })
            }]
        });
        this.items = [this.dataForm];
        this.buttons = [{
            text:appLang.ADD,
            scope:this,
            handler:this.formSubmit
        },{
            text:appLang.CANCEL,
            scope:this,
            handler:this.close
        }];
        this.callParent(arguments);
    },
    formSubmit:function(){
        var me = this;
        this.dataForm.getForm().submit({
            clientValidation: true,
            waitTitle:appLang.SAVING,
            method:'post',
            url:this.controllerUrl +  'addSub',
            success: function(form, action)
            {
                if(!action.result.success){
                    Ext.Msg.alert(appLang.MESSAGE, action.result.msg);
                    return;
                }
                me.fireEvent('objectAdded');
                me.close();
            },
            failure: app.formFailure
        });
    }
});