/**
 * Column filter edit window
 *
 * @event filterChange
 */
Ext.define('designer.grid.column.FilterWindow',{
    extend:'Ext.Window',
    layout:'border',
    width:app.checkWidth(350),
    height:app.checkHeight(500),
    bodyCls:'formBody',
    bodyPadding:5,
    modal:true,
    objectName:null,
    columnId:null,
    controllerUrl:null,
    propertiesPanel:null,

    initComponent:function() {

        this.typeSelector = Ext.create('Ext.form.field.ComboBox',{
            typeAhead: true,
            triggerAction: 'all',
            selectOnTab: true,
            labelWidth:50,
            forceSelection:true,
            queryMode:'local',
            fieldLabel:desLang.type,
            region:'north',
            displayField:'title',
            valueField:'id',
            store:Ext.create('Ext.data.Store', {
                model: 'app.comboStringModel',
                data: [
                    {id: 'boolean', title: 'boolean'},
                    {id: 'date', title: 'date'},
                  //  {id: 'datetime', title: 'datetime'},
                    {id: 'list', title: 'list'},
                    {id: 'number', title: 'number'},
                    {id: 'string', title: 'string'}
                ]
            }),
            listeners:{
                change:{
                    fn:function(){
                        this.setFilterType();
                    },
                    scope:this
                }
            }
        });

        this.propertiesPanel = Ext.create('designer.properties.Panel',{
            autoLoadData:false,
            controllerUrl: app.createUrl([designer.controllerUrl ,'gridcolumnfilter','']),
            eventsControllerUrl:app.createUrl([designer.controllerUrl ,'gridcolumnfilterevents','']),
            extraParams:{
                column:this.columnId
            },
            region:'center',
            objectName:this.objectName,
            listeners:{
                dataSaved:{
                    fn:function(){},
                    scope:this
                }
            },
            layout:'fit'
        });

        this.items = [
            this.typeSelector,
            this.propertiesPanel
        ];

        this.buttons = [
            {
                text:desLang.removeFilter,
                handler:this.removeFilter,
                scope:this
            },
            {
                text:desLang.close,
                handler:function(){
                    this.close();
                },
                scope:this
            }

        ];

        this.callParent();
        this.on('show',this.loadFilter,this);
    },
    /**
     * Get filter
     */
    loadFilter:function(){
        this.getEl().mask(desLang.saving);
        Ext.Ajax.request({
            url:app.createUrl([designer.controllerUrl ,'gridcolumnfilter','gettype']),
            method: 'post',
            scope:this,
            params:{
                column:this.columnId,
                object:this.objectName
            },
            success: function(response, request) {
                response =  Ext.JSON.decode(response.responseText);
                if(!response.success){
                    Ext.Msg.alert(appLang.MESSAGE,response.msg);
                    this.getEl().unmask();
                    this.close();
                }else{
                    if(!Ext.isEmpty(response.data.type)){
                        this.typeSelector.setRawValue(response.data.type);
                        this.propertiesPanel.loadProperties();
                        this.propertiesPanel.refreshEvents();
                    }
                    this.getEl().unmask();
                }
            },
            failure:function() {
                Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
                this.getEl().unmask();
            }
        });
    },
    /**
     * Set filter
     */
    setFilterType:function(){
        this.getEl().mask(desLang.saving);
        Ext.Ajax.request({
            url:app.createUrl([designer.controllerUrl ,'gridcolumnfilter','settype']),
            method: 'post',
            scope:this,
            params:{
                column:this.columnId,
                object:this.objectName,
                type:this.typeSelector.getValue()
            },
            success: function(response, request) {
                response =  Ext.JSON.decode(response.responseText);
                if(!response.success){
                    Ext.Msg.alert(appLang.MESSAGE,response.msg);
                    this.getEl().unmask();
                    this.close();
                }else{
                    this.getEl().unmask();
                    designer.msg(desLang.success , desLang.filterSaved);
                    this.propertiesPanel.loadProperties();
                    this.propertiesPanel.refreshEvents();
                    this.fireEvent('filterChange');
                }
            },
            failure:function() {
                Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
                this.getEl().unmask();
            }
        });
    },
    /**
     * Clear filter
     */
    removeFilter:function(){
        this.getEl().mask(desLang.saving);
        Ext.Ajax.request({
            url:app.createUrl([designer.controllerUrl ,'gridcolumnfilter','removefilter']),
            method: 'post',
            scope:this,
            params:{
                column:this.columnId,
                object:this.objectName
            },
            success: function(response, request) {
                response =  Ext.JSON.decode(response.responseText);
                if(!response.success){
                    Ext.Msg.alert(appLang.MESSAGE,response.msg);
                    this.getEl().unmask();
                }else{
                    this.getEl().unmask();
                    this.fireEvent('filterChange');
                    this.close();
                }
            },
            failure:function() {
                Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
                this.getEl().unmask();
            }
        });
    }
});