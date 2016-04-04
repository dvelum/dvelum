Ext.ns('app.crud.tasks');

Ext.define('app.crud.tasks.Model', {
    extend: 'Ext.data.Model',
    fields: [
        {name:'id', type:'integer'},
        {name:'status' , type:'string'},
        {name:'status_code',type:'integer'},
        {name:'title' , type:'string'},
        {name:'parent' , type:'integer'},
        {name:'op_total', type:'integer'},
        {name:'op_finished', type:'integer'},
        {name:'progress', type:'float'},
        {name:'memory', type:'string'},
        {name:'memory_peak', type:'string'},
        {name:'time_started', type:'date' , dateFormat:'Y-m-d H:i:s'},
        {name:'time_finished', type:'date' , dateFormat:'Y-m-d H:i:s'},
        {name:'runtime',type:'string'}
    ]
});

Ext.define('app.crud.tasks.Main',{
    extend:'Ext.panel.Panel',
    layout:'fit',

    canEdit:false,
    controllerUrl:'',

    dataStore:null,
    dataGrid:null,
    refreshInterval:2,

    initComponent:function(){
        this.dataStore =  Ext.create('Ext.data.Store', {
            model: 'app.crud.tasks.Model',
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
            autoLoad: false,
            sorters: [{
                property : 'time_started',
                direction: 'DESC'
            }]
        });

        var me = this;
        var gridColumns = [];

        if(this.canEdit){
            gridColumns.push({
                width:35,
                dataIndex:'status_code',
                id:'processAction',
                renderer:function(value){
                    /*
                     * Check BGTask for run (Bgtask::STATUS_RUN = 1) and sleep (Bgtask::STATUS_SLEEP = 2)
                     */
                    if(value==1){
                        return '<img src="'+app.wwwRoot+'i/system/pause.png" title="'+appLang.PAUSE+'" style="cursor:pointer;">';
                    }else if(value == 2){
                        return '<img src="'+app.wwwRoot+'i/system/play.png" title="'+appLang.CONTINUE+'" style="cursor:pointer;">';
                    }
                    return '';
                }
            },{
                width:35,
                dataIndex:'status_code',
                id:'stopAction',
                renderer:function(value){
                    /*
                     * Check BGTask for run (Bgtask::STATUS_RUN = 1) and sleep (Bgtask::STATUS_SLEEP = 2)
                     */
                    if(value==1 || value == 2){
                        return '<img src="'+app.wwwRoot+'i/system/stop.png" title="'+appLang.STOP+'" style="cursor:pointer;">';
                    }
                    return '';
                }
            });
        }

        gridColumns.push({
                text:appLang.PID,
                sortable: true,
                dataIndex:'id',
                width:30
            },{
                sortable: true,
                text:appLang.TITLE,
                dataIndex: 'title',
                width:200,
                flex:1,
                align:'left'
            }, {
                sortable: true,
                text:appLang.STATUS,
                columns:[
                    {
                        dataIndex: 'status',
                        width:60,
                        align:'center'

                    },{
                        sortable: false,
                        dataIndex: 'progress',
                        renderer:app.progressRenderer,
                        align:'center',
                        width:80
                    },{
                        sortable: false,
                        dataIndex: 'progress',
                        align:'left',
                        width:60,
                        renderer:function(value, metaData, record, rowIndex, colIndex, store){
                            return  record.get('op_finished')+'/'+record.get('op_total');
                        }
                    }]
            },{
                text: appLang.MEMORY,
                columns:[
                    {
                        sortable: true,
                        text:appLang.MEMORY_PEAK_USAGE,
                        dataIndex: 'memory_peak',
                        width:70,
                        align:'right'
                    },{
                        sortable: true,
                        text:appLang.MEMORY_ALLOCATED,
                        dataIndex: 'memory',
                        width:70,
                        align:'right'
                    }
                ]
            },{
                sortable: true,
                align:'center',
                text:appLang.TIME_STARTED,
                dataIndex: 'time_started',
                xtype:'datecolumn',
                format:'d.m.Y H:i:s',
                width:120
            },{
                dataIndex:'runtime',
                text:appLang.RUNTIME,
                align:'center',
                width:120
            }
        );

        if(this.canEdit){
            gridColumns.push({
                xtype:'actioncolumn',
                width:20,
                items:[
                    {
                        iconCls:'deleteIcon',
                        tooltip:appLang.KILL_TASK,
                        handler:this.kill,
                        scope:this
                    }
                ]
            });
        }

        this.dataGrid = Ext.create('Ext.grid.Panel',{
            store: this.dataStore,
            viewConfig:{stripeRows:false},
            frame: false,
            loadMask:false,
            columnLines: true,
            scrollable:true,
            columns: gridColumns
        });

        if(this.canEdit)
        {
            this.dataGrid.on('cellclick',function(grid, cell, columnIndex, record , node , rowIndex , evt){

                var column = grid.getHeaderCt().getHeaderAtIndex(columnIndex).id;

                var state = record.get('status_code');
                var pid = record.get('id');

                switch(column){

                    case 'processAction' :
                        if(state==1){
                            me.pause(pid);
                        }
                        if(state ==2){
                            me.resume(pid);
                        }
                        break;
                    case 'stopAction':
                        me.stop(pid);
                        break;
                }

            },this);
        }

        this.items = [this.dataGrid];
        this.callParent(arguments);
    },
    reloadInfo:function(){
        var me = this;
        Ext.Ajax.request({
            url: this.controllerUrl + 'list',
            method: 'post',
            success: function(response, request) {
                response =  Ext.JSON.decode(response.responseText);
                if(response.success){
                    me.dataStore.loadData(response.data);
                }
            },
            failure:app.formFailure
        });
    },
    kill:function(grid, rowIndex, colIndex){
        var me = this;
        var record = grid.getStore().getAt(rowIndex);
        var pid = record.get('id');
        grid.getEl().mask(appLang.SAVING);
        Ext.Ajax.request({
            url: this.controllerUrl + 'kill',
            params:{'pid':pid},
            method: 'post',
            success: function(response, opts) {
                response =  Ext.JSON.decode(response.responseText);
                if(response.success){
                    grid.getStore().remove(record);
                }
                grid.getEl().unmask();
            },
            failure:function(response, opts){
                grid.getEl().unmask();
                app.ajaxFailure(arguments);
            }
        });
    },
    /**
     * Pause task
     * @param integer pid
     */
    pause:function(pid){
        var me = this;
        me.dataGrid.getEl().mask(appLang.SAVING);
        Ext.Ajax.request({
            url: this.controllerUrl + 'pause',
            params:{'pid':pid},
            method: 'post',
            success: function(response, opts) {
                response =  Ext.JSON.decode(response.responseText);
                setTimeout(function(){
                    me.dataGrid.getEl().unmask();
                },me.refreshInterval);
            },
            failure:function(response, opts){
                me.dataGrid.getEl().unmask();
                app.ajaxFailure(arguments);
            }
        });
    },
    /**
     * Resume task
     * @param integer pid
     */
    resume:function(pid){
        var me = this;
        me.dataGrid.getEl().mask(appLang.SAVING);
        Ext.Ajax.request({
            url: this.controllerUrl + 'resume',
            params:{'pid':pid},
            method: 'post',
            success: function(response, opts) {
                response =  Ext.JSON.decode(response.responseText);
                setTimeout(function(){
                    me.dataGrid.getEl().unmask();
                },me.refreshInterval);
            },
            failure:function(response, opts){
                me.dataGrid.getEl().unmask();
                app.ajaxFailure(arguments);
            }
        });
    },
    stop:function(pid){
        var me = this;
        me.dataGrid.getEl().mask(appLang.SAVING);
        Ext.Ajax.request({
            url: this.controllerUrl + 'stop',
            params:{'pid':pid},
            method: 'post',
            success: function(response, opts) {
                response =  Ext.JSON.decode(response.responseText);
                setTimeout(function(){
                    me.dataGrid.getEl().unmask();
                },me.refreshInterval);
            },
            failure:function(response, opts){
                me.dataGrid.getEl().unmask();
                app.ajaxFailure(arguments);
            }
        });
    }
});
