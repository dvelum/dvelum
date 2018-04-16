Ext.define('app.crud.orm.ObjectsModel', {
    extend: 'Ext.data.Model',
    fields: [
        {name:'title' ,  type:'string'},
        {name:'name' ,  type:'string'},
        {name:'table' , type:'string'},
        {name:'engine', type:'string'},
        {name:'vc', 	type:'boolean'},
        {name:'fields', type:'integer'},
       // {name:'records',type:'string'},
       // {name:'data_size', type:'string'},
       // {name:'index_size', type:'string'},
       // {name:'size', type:'string'},
        {name:'validdb', typr:'boolean'},
        {name:'title', typr:'string'},
        {name:'save_history', type:'boolean'},
        {name:'link_title', type:'string'},
        {name:'rev_control',type:'boolean'},
        {name:'system' , type:'boolean'},
        {name:'db_host' , type:'string'},
        {name:'db_name' , type:'string'},
        {name:'broken' , type:'boolean'},
        {name:'locked' , type:'boolean'},
        {name:'readonly' , type:'boolean'},
        {name:'can_connect' , type:'boolean'},
        {name:'primary_key', type:'string'},
        {name:'connection' , type:'string'},
        {name:'distributed', type:'boolean'}
    ]
});
Ext.define('app.crud.orm.ObjectsDetailsModel', {
    extend: 'Ext.data.Model',
    fields: [
        {name:'records',type:'string'},
        {name:'data_size', type:'string'},
        {name:'index_size', type:'string'},
        {name:'size', type:'string'},
        {name:'validdb', typr:'boolean'},
        {name:'name' ,  type:'string'}
    ]
});
/**
 *
 * @event editRecord
 * @param name
 *
 * @event rebuildTable
 * @param name
 *
 * @event removeItem
 * @param name
 *
 * @event viewData
 * @param Ext.data Model record
 *
 */
Ext.define('app.crud.orm.dataGrid',{

    extend:'Ext.grid.Panel',

    frame: false,
    loadMask:true,
    columnLines: true,
    scrollable:true,
    bodyBorder:false,
    border:false,

    editable: false,

    initComponent:function(){


        this.initNestedGridPlugin();





        this.viewConfig = {stripeRows: true, enableTextSelection: true};

        this.columns = [];

        if(this.editable)
        {
            this.columns.push({
                xtype:'actioncolumn',
                align:'center',
                width:86,
                items:[
                    {
                        tooltip:appLang.EDIT_RECORD,
                        iconCls:'editIcon',
                        scope:this,
                        handler:function(grid, rowIndex, colIndex){
                            this.fireEvent('editRecord' , grid.getStore().getAt(rowIndex));
                        }
                    },{
                        tooltip:appLang.REBUILD_DB_TABLE,
                        iconCls:'buildIcon',
                        scope:this,
                        handler:function(grid, rowIndex, colIndex){
                            this.fireEvent('rebuildTable' , grid.getStore().getAt(rowIndex).get('name'));
                        }
                    },{
                        tooltip:appLang.VIEW_DATA,
                        iconCls:'gridIcon',
                        scope:this,
                        handler:function(grid, rowIndex, colIndex){
                            var rec = grid.getStore().getAt(rowIndex);
                            this.fireEvent('viewData' , rec);
                        }
                    },{
                        tooltip:appLang.SHARD,
                       // iconCls:'shardIcon',
                        scope:this,
                        handler:function(grid, rowIndex, colIndex){
                            var rec = grid.getStore().getAt(rowIndex);
                            this.fireEvent('viewShards' , rec);
                        },
                        getClass: function(v, meta, record) {
                            if(!record.get('distributed')) {
                                return 'x-hide-display';
                            }else{
                                return 'shardIcon';
                            }
                        }
                    }
                ]
            });
        }



        var titleRenderer = function(value, metaData, record, rowIndex, colIndex, store){
            if(record.get('external')){
                metaData.style ='color:#0415D0;';
            }


            if(record.get('readonly')){
                value = '<img src="'+app.wwwRoot+'i/system/plock.png" title="'+appLang.DB_READONLY_TOOLTIP+'" height="15"> ' + value;

            }

            if(record.get('locked') && !record.get('readonly')){
                value = '<img src="'+app.wwwRoot+'i/system/locked.png" title="'+appLang.DB_STRUCTURE_LOCKED_TOOLTIP+'" height="15"> ' + value;
            }



            if(record.get('broken'))
            {
                metaData.style ='background-color:red;';
                value = '<img src="'+app.wwwRoot+'i/system/broken.png" title="'+appLang.BROKEN_LINK+'" height="15">&nbsp; ' + value;
            }else if(!record.get('can_connect')){
                metaData.style ='background-color:red;';
                value = '<img src="'+app.wwwRoot+'i/system/broken.png" title="'+appLang.CANT_CONNECT+'" height="15">&nbsp; ' + value;
            }
            return value;
        };

        this.columns.push(
            {
                text:appLang.TITLE,
                width:200,
                dataIndex:'title',
                renderer:titleRenderer
            },{
                text: appLang.OBJECT,
                dataIndex: 'name',
                width:200,
                align:'left'
            },{
                text:appLang.DATA_TABLE,
                dataIndex:'table',
                align:'left',
                hidden:true
            },{
                text: appLang.PROPERTIES,
                dataIndex: 'fields',
                align:'center'
            },
 /*
            {
                text:appLang.DB_STATE,
                columns:[
                    {
                        text: appLang.RECORDS,
                        dataIndex: 'records',
                        align:'center',
                        renderer:rendererRecords
                    },{
                        text:appLang.DATA_SIZE,
                        dataIndex:'data_size',
                        align:'center'
                    },{
                        text:appLang.INDEX_SIZE,
                        dataIndex:'index_size',
                        align:'center'
                    },{
                        text:appLang.SPACE_USAGE,
                        dataIndex:'size',
                        align:'center'
                    }
                ]
            },
  */
            {
                text: appLang.DB_ENGINE,
                dataIndex: 'engine',
                align:'center'
            },{
                sortable: true,
                text: appLang.VC,
                dataIndex: 'vc',
                width:100,
                align:'center',
                renderer:app.checkboxRenderer
            },{
                text:appLang.VALID_DB,
                dataIndex: 'validdb',
                align:'center',
                renderer:app.checkboxRenderer
            },{
                text:appLang.IS_SYSTEM,
                dataIndex:'system',
                align:'center',
                width:100,
                renderer:app.checkboxRenderer
            },{
                text:appLang.DB_HOST,
                align:'center',
                dataIndex:'db_host',
                width:100,
                hidden:true
            },{
                text:appLang.DB_NAME,
                align:'center',
                dataIndex:'db_name',
                width:100,
                hidden:true
            },{
                text:appLang.DB_CONNECTION,
                align:'center',
                dataIndex:'connection',
                width:120,
                hidden:true
            },{
                text:appLang.DISTRIBUTED,
                align:'center',
                dataIndex:'distributed',
                width:120,
                hidden:true,
                renderer:app.checkboxRenderer
            }
        );

        if(this.editable)
        {
            this.columns.push(
                {
                    xtype:'actioncolumn',
                    align:'center',
                    width:30,
                    items:[{
                        tooltip:appLang.DELETE_RECORD,
                        iconCls:'deleteIcon',
                        scope:this,
                        handler:function(grid, rowIndex, colIndex){
                            this.fireEvent('removeItem', grid, rowIndex, colIndex);
                        }
                    }]
                }
            );
        }
        this.callParent();
    },
    initNestedGridPlugin:function(){
        this.plugins = [{
            ptype: 'rowexpandergrid',
            gridConfig: {
                columns: [
                    {
                        text: appLang.NAME,
                        dataIndex: 'name',
                        align: 'center'
                    },
                    {
                        text: appLang.RECORDS,
                        dataIndex: 'records',
                        align: 'center',
                        renderer: function(value, metaData, record, rowIndex, colIndex, store){
                            if(record.get('external')){
                                metaData.style ='color:#0415D0;';
                            }
                            if(record.get('engine') == 'InnoDB'){
                                value = '~ ' + value;
                            }
                            return value;
                        }
                    }, {
                        text: appLang.DATA_SIZE,
                        dataIndex: 'data_size',
                        align: 'center'
                    }, {
                        text: appLang.INDEX_SIZE,
                        dataIndex: 'index_size',
                        align: 'center'
                    }, {
                        text: appLang.SPACE_USAGE,
                        dataIndex: 'size',
                        align: 'center'
                    }
                ],
                columnLines: true,
                border: true,
             //   autoWidth: false,
             //   autoHeight: true,
                frame: false
                //header:false,
            },
            initStore: function (record, config) {
                config.store = Ext.create('Ext.data.Store', {
                    model: 'app.crud.orm.ObjectsDetailsModel',
                    proxy: {
                        type: 'ajax',
                        url: app.crud.orm.Actions.listObjDetails,
                        reader: {
                            type: 'json',
                            rootProperty: 'data',
                            idProperty: 'name'
                        },
                        extraParams: {
                            object: record.get('name')
                        },
                        simpleSortMode: true
                    },
                    autoLoad: true
                });
            }
        }];
    }
});