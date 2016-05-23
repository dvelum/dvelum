Ext.ns('app.crud.reports');

/**
 * @event beforeexpand
 * @param {app.FieldSet} component
 */

/**
 * @event beforecollapse
 * @param {app.FieldSet} component
 */

Ext.define('app.FieldSet',{
    extend:'Ext.form.FieldSet',
    setExpanded:function(expanded){
        var me = this;
        if(expanded){
            this.fireEvent('beforeexpand');
        }else{
            this.fireEvent('beforecollapse');
        }
        return this.callParent(arguments);
    }
});


Ext.define('app.crud.reports.Main',{
    extend:'Ext.Panel',
    layout:'fit',

    controllerUrl:'',

    canEdit:false,
    canDelete:false,

    dataItems:[],
    dataTitle:'',

    buttonLoad:null,
    buttonSave:null,
    buttonCreate:null,
    buttonClose:null,
    buttonConfig:null,


    initComponent:function()
    {
        this.tbar = [];

        this.buttonLoad = Ext.create('Ext.Button',{
            tooltip:appLang.LOAD_REPORT,
            iconCls:'openIcon',
            handler:this.selectReport,
            scope:this
        });

        this.buttonSave = Ext.create('Ext.Button',{
            tooltip:appLang.SAVE_REPORT,
            iconCls:'saveIcon',
            handler:this.saveReport,
            scope:this,
            hidden:true
        });

        this.buttonCreate = Ext.create('Ext.Button',{
            tooltip:appLang.CREATE_REPORT,
            iconCls:'newIcon',
            handler:this.createReport,
            scope:this
        });

        this.buttonClose = Ext.create('Ext.Button',{
            iconCls:'exitIcon',
            tooltip:appLang.CLOSE,
            handler:this.closeReport,
            scope:this,
            hidden:true
        });

        this.buttonConfig = Ext.create('Ext.Button',{
            text:appLang.REPORT_CONFIG,
            iconCls:'configureIcon',
            handler:this.editReport,
            scope:this,
            hidden:true
        });

        this.tbar.push(
            this.buttonLoad ,
            this.buttonSave,
            this.buttonClose
        );

        if(this.canEdit)
        {
            this.tbar.push(this.buttonCreate,this.buttonConfig);
        }

        this.reportResult = Ext.create('app.report.Results',{controllerUrl:this.controllerUrl});
        this.items = [this.reportResult];
        this.callParent(arguments);
    },
    editReport:function(){
        Ext.create('app.report.ConfigWindow',{
            width:app.checkWidth(1200),
            height:app.checkHeight(800),
            dataItems:this.dataItems,
            dataTitle:this.dataTitle,
            controllerUrl:this.controllerUrl,
            listeners:{
                'dataChanged':{
                    fn:function(){
                        this.checkIsLoaded();
                    },
                    scope:this
                }
            }
        }).show();
    },
    /**
     * Reset interface layout
     */
    resetInterface:function(){
        if(this.canEdit){
            this.buttonCreate.show();
        }
        this.reportResult.loadResult();
        this.buttonLoad.show();
        this.buttonSave.hide();
        this.buttonConfig.hide();
        this.buttonClose.hide();
    },
    /**
     * Check if report is loaded
     */
    checkIsLoaded: function(){

        Ext.Ajax.request({
            url: this.controllerUrl + "checkloaded",
            method: 'post',
            scope:this,
            success: function(response, request) {
                response =  Ext.JSON.decode(response.responseText);

                if(!response.success){
                    return;
                }

                if(!Ext.isEmpty(response.data.items)){
                    this.dataItems = response.data.items;
                    this.dataTitle = response.data.partconfig.title + ' ('+response.data.partconfig.object+')';
                }else{
                    this.dataItems = [];
                    this.dataTitle = '';
                }

                if(this.canEdit)
                {
                    this.buttonConfig.show();
                    this.buttonSave.show();
                }
                this.buttonCreate.hide();
                this.buttonLoad.hide();
                this.buttonClose.show();

                this.reportResult.loadResult();

            },
            failure:function() {
                Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
            }
        });
    },

    selectReport:function(){
        var win = Ext.create('app.filesystemWindow',{
            title:appLang.SELECT_REPORT,
            controllerUrl:this.controllerUrl,
            listeners:{
                fileSelected:{
                    fn:this.loadReport,
                    scope:this
                }
            }
        }).show();

    },
    createReport:function(){
        var win = Ext.create('app.filesystemWindow',{
            title:appLang.SELECT_REPORT,
            controllerUrl:this.controllerUrl,
            viewMode:'create',
            createExtension:'.report.dat',
            listeners:{
                fileCreated:{
                    fn:this.loadReport,
                    scope:this
                }
            }
        }).show();
    },
    /**
     * Close report
     */
    closeReport:function(){
        var me = this;
        if(!this.canEdit){
            me.clearSession();
            me.resetInterface();
            return;
        }
        Ext.Msg.confirm(appLang.CONFIRMATION, appLang.MSG_SAVE_BEFORE_CLOSE, function(btn){

            if(btn == 'yes'){
                var handle = function(){
                    me.clearSession();
                    me.resetInterface();
                };
                me.saveReport(handle);
            }else{
                me.clearSession();
                me.resetInterface();
            }

        }, this);
    },

    /**
     * Load report
     * @param string name - report ID
     */
    loadReport:function(name){
        var me = this;
        me.getEl().mask(appLang.LOADING);
        Ext.Ajax.request({
            url: this.controllerUrl + "load",
            method: 'post',
            params:{
                file:name
            },
            success: function(response, request) {
                response =  Ext.JSON.decode(response.responseText);
                if(response.success){
                    me.checkIsLoaded();
                }else{
                    Ext.Msg.alert(appLang.MESSAGE,response.msg);
                }
                me.getEl().unmask();
            },
            failure:function() {
                Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
                me.getEl().unmask();
            }
        });
    },
    /**
     * Save report
     * @param function callback
     */
    saveReport:function(callback){

        var me = this;
        me.getEl().mask(appLang.SAVING);
        Ext.Ajax.request({
            url: this.controllerUrl + "save",
            method: 'post',
            success: function(response, request) {
                response =  Ext.JSON.decode(response.responseText);
                if(!response.success){
                    Ext.Msg.alert(appLang.MESSAGE,response.msg);
                }
                me.getEl().unmask();
                if(typeof callback == 'function'){
                    callback();
                }
            },
            failure:function() {
                Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
                me.getEl().unmask();
            }
        });
    },
    /**
     * Clear report session
     */
    clearSession:function(){
        Ext.Ajax.request({
            url: this.controllerUrl + "close",
            method: 'post',
            success: function(response, request) {

            },
            failure:function() {
                Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
            }
        });
    }
});
