Ext.ns('app.crud.cache');



Ext.define('app.crud.cache.Main',{
    extend: 'Ext.grid.Panel',
    canDelete:false,
    controllerUrl:null,
    columnLines:true,
    scrollable:true,

    initComponent:function(){


        this.store = Ext.create("Ext.data.Store", {
            autoLoad : true,
            fields : [
                {"name" : "id" , "type" : "integer"},
                {"name" : "group" , "type" : "string"},
                {"name" : "title" , "type" : "string"},
                {"name" : "value" , "type" : "string"}
            ],
            proxy : {
                directionParam : "pager[dir]",
                limitParam : "pager[limit]",
                simpleSortMode : true,
                sortParam : "pager[sort]",
                startParam : "pager[start]",
                url : this.controllerUrl + "info",
                reader : {
                    idProperty : "id",
                    rootProperty : "data"
                },
                type : "ajax"
            },
            groupField : "group"
        });

        this.columns = [
            {
                xtype : "gridcolumn",
                dataIndex : "group",
                text : "Server"
            }, {
                xtype : "gridcolumn",
                dataIndex : "title",
                text : appLang.PROPERTY,
                width : 170
            }, {
                xtype : "gridcolumn",
                dataIndex : "value",
                text : appLang.VALUE,
                width : 120
            }
        ];


        this.features= [
            Ext.create('Ext.grid.feature.Grouping', {
                groupHeaderTpl : appLang.SERVER + ' {name}',
                startCollapsed : 0,
                enableGroupingMenu : 0,
                hideGroupedHeader : 1,
            })
        ];


        this.tbar = [];

        if(this.canDelete){
            this.tbar.push({
                text : appLang.RESET_CACHE,
                handler:this.resetCache,
                scope:this,
                icon : app.wwwRoot+"i/system/trash.png"
            },'-');
        }

        this.tbar.push(
            Ext.create("SearchPanel", {
                xtype : "searchpanel",
                store : this.store,
                fieldNames : [ "title" ],
                local : true
            })
        );

        this.callParent();
    },
    resetCache:function(){

        if(!this.canDelete){
            return;
        }

        this.getEl().mask(appLang.SAVING);

        Ext.Ajax.request({
            url:this.controllerUrl + 'reset',
            method: 'post',
            scope:this,
            success: function(response, request) {
                response =  Ext.JSON.decode(response.responseText);
                if(response.success){
                    this.getStore().load();
                }else{
                    Ext.Msg.alert(appLang.MESSAGE , response.msg);
                }
                this.getEl().unmask();
            },
            failure:function(){
                Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
                this.getEl().unmask();
            }
        });
    }
});
