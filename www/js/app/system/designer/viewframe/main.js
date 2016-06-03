Ext.ns('app');
app.viewFrame = null;

/**
 * @event launch
 * @event projectLoaded
 */
Ext.application({
    name: 'ViewFrame',
    mainUrl:'',

    initComponent:function(){
        this.callParent();

    },

    launch:function() {
        app.application = this;
        app.viewFrame = Ext.create('Ext.container.Viewport', {
            layout : 'fit',
            items : [],
            renderTo : Ext.getBody()
        });

        if (window.addEventListener) {
            window.addEventListener("message", function(event){app.application.onCommand(event);});
        } else {
            // IE8
            window.attachEvent("message", function(event){app.application.onCommand(event);});
        }

        this.callParent();
        this.fireEvent('launch');
    },

    onCommand:function(event){
        var message = event.data;

        if (event.origin != window.location.origin) {
            return;
        }

        if(message.command && message.params){
            app.application.runCommand(message.command , message.params);
        }
    },
    /**
     * Run command from designer
     * @param {String} command
     * @param {Object} params
     */
    runCommand:function(command , params)
    {
        switch(command){
            case 'showWindow':
                var win = Ext.create(applicationClassesNamespace + '.'+params.name,{
                    objectName:params.name
                });
                win.show();
                win.on('resize',this.onWindowResize,this);
                break;
        }
    },
    /**
     * Window size changed
     * @param {Ext.Window} window
     * @param {Number} width
     * @param {Number} height
     * @param {Object} opts
     */
    onWindowResize:function(window , width , height , opts)
    {
        var url = '/'+app.createUrl([this.mainUrl ,'window','changesize']);
        var me = this;
        Ext.Ajax.request({
            url:url,
            method: 'post',
            params:{
                'object':window.objectName,
                'width':width,
                'height':height
            },
            success: function(response, request) {
                response =  Ext.JSON.decode(response.responseText);
                if(response.success){
                    me.sendCommand({command:'windowSizeChanged',params:[]});
                }else{
                    Ext.Msg.alert(appLang.MESSAGE, response.msg);
                }
            },
            failure:function() {
                Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
            }
        });
    },
    /**
     * On grid column resize
     * @param {String} objectName
     * @param {Ext.grid.header.Container} ct
     * @param {Ext.grid.column.Column} column
     * @param {Number} width
     * @param {Object} eOpts
     */
    onGridColumnResize:function(objectName, ct, column, width, eOpts)
    {
        if(typeof column.flex !== 'undefined')
            return;

        var url = '/'+app.createUrl([this.mainUrl ,'gridcolumn','changesize']);
        var me = this;
        Ext.Ajax.request({
            url:url,
            method: 'post',
            params:{
                'object':objectName,
                'column':column.projectColId,
                'width':width
            },
            success: function(response, request) {
                response =  Ext.JSON.decode(response.responseText);
                if(response.success){
                    me.sendCommand({command:'columnSizeChanged',params:{object:objectName}});
                }else{
                    Ext.Msg.alert(appLang.MESSAGE, response.msg);
                }
            },
            failure:function() {
                Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
            }
        });
    },
    /**
     * On Grid column move
     * @param {String} object
     * @param {Ext.grid.header.Container} ct
     * @param {Ext.grid.column.Column} column
     * @param {Number} fromIdx
     * @param {Number} toIdx
     * @param {object} eOpts
     */
    onGridColumnMove:function(object , ct, column, fromIdx, toIdx, eOpts)
    {
        var url = '/'+app.createUrl([this.mainUrl ,'gridcolumn','move']);
        var me = this;

        var columns = ct.getGridColumns();
        var order = [];

        Ext.each(columns,function(item, index){
            order.push(item.projectColId);
        });

        Ext.Ajax.request({
            url:url,
            method: 'post',
            params:{
                'object':object,
                'order':Ext.JSON.encode(order)
            },
            success: function(response, request) {
                response =  Ext.JSON.decode(response.responseText);
                if(response.success){
                    me.sendCommand({command:'columnMoved',params:{object:object}});
                }else{
                    Ext.Msg.alert(appLang.MESSAGE, response.msg);
                }
            },
            failure:function() {
                Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
            }
        });
    },
    /**
     * Send command for layout frame
     * @param {object} command -  {command:'some string','params':'mixed'}
     */
    sendCommand:function(command){
        window.parent.postMessage(command, window.location.origin);
    }
});