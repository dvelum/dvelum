/**
 * Related Elements Grid
 * @author Kirill Egorov 2011
 * @var {Ext.Panel}
 */

Ext.define('app.relatedGridModel', {
    extend: 'Ext.data.Model',
    fields: [
        {name:'id' , type:'integer'},
        {name:'published' , type:'boolean'},
        {name:'deleted' , type:'boolean'},
        {name:'title' , type:'string'}
    ]
});
/**
 *
 * @event addItemCall
 *
 * @event change
 * @param app.relatedGridPanel this
 *
 */
Ext.define('app.relatedGridPanel',{
    /*
     * 'Ext.grid.Panel' has bad renderer in tabs
     */
    extend:'Ext.Panel',
    alias:'widget.relatedgridpanel',

    dataUrl:false,
    dataGrid:null,
    dataStore:null,
    fieldName:null,

    layout:'fit',
    gridRendered:false,
    gridReadOnly:false,
    readOnly:false,
    /**
     * @property Ext.Button addButton
     */
    addButton:null,
    /**
     * @property Ext.Button addButton
     */
    addAllButton:null,
    /**
     * Extra params for requests
     * @property {Object}
     */
    extraParams:null,
    /**
     * Show status column
     */
    statusColumn:true,
    /**
     * Show sort column
     */
    sortColumn:true,
    /**
     * Show delete column
     */
    deleteColumn:false,
    /**
     * Add button label
     */
    addButtonText:appLang.ADD_ITEM,
    /**
     * add all elements button text
     */
    addAllButtonText:appLang.ADD_ALL,
    /**
     * Data column header
     */
    dataColumnTitle:appLang.TITLE,
    /**
     * Data column store index
     */
    dataColumnIndex:'title',
    /**
     * Controller url
     */
    controllerUrl:null,
    /**
     * Store rootProperty
     */
    rootProperty:null,
    /**
     * Show add all button
     */
    showAddAllButton:false,

    constructor: function(config) {
        config = Ext.apply({
            extraParams:{}
        }, config || {});
        this.callParent(arguments);
    },

    initComponent:function(){

        this.addButton = Ext.create('Ext.Button',{
            text:this.addButtonText,
            disabled:this.readOnly,
            listeners:{
                'click':{
                    fn:function(){
                        this.fireEvent('addItemCall');
                    },
                    scope:this
                }
            }
        });

        this.addAllButton = Ext.create('Ext.Button',{
            text:this.addAllButtonText,
            disabled:this.readOnly,
            listeners:{
                'click':{
                    fn:function(){
                        this.fireEvent('addAllItemCall');
                    },
                    scope:this
                }
            }
        });

        this.dataStore = Ext.create('Ext.data.Store',{
            model:'app.relatedGridModel',
            proxy: {
                type: 'ajax',
                reader: {
                    type: 'json',
                    idProperty: 'id',
                    rootProperty: this.rootProperty
                },
                url: this.controllerUrl,
                extraParams:this.extraParams
            },
            autoLoad:false
        });

        this.dataStore.on('datachanged' , function(){
            this.fireEvent('change' , this);
        },this);

        this.tbar = [this.addButton];
        if(this.showAddAllButton){
            this.tbar.push('-');
            this.tbar.push(this.addAllButton);
        }

        this.callParent();
        this.updateViewState();
    },
    /**
     * Load grid data
     * @param {Array} data
     */
    setData: function(data){
        this.dataStore.removeAll();
        if(!Ext.isEmpty(data)){
            this.dataStore.loadData(data);
        }
    },
    addRecord:function(record){

        if(this.dataStore.findExact('id',record.get('id'))!=-1){
            return;
        }

        var rPubblished = true;

        if(record.get('published')!=undefined){

            rPubblished = record.get('published');
        }

        var r = Ext.create('app.relatedGridModel', {
            id: record.get('id'),
            title:record.get('title'),
            deleted:0,
            published:rPubblished
        });

        this.dataStore.insert(this.dataStore.getCount(), r);
    },
    addRecords:function(records){
        Ext.Array.each(records, function(val){
            this.addRecord(val);
        },this);
    },
    getStore:function(){
        return this.dataGrid.getStore();
    },
    getGrid: function(){
        return this.dataGrid;
    },
    collectData: function(){
        var recordList = [];
        this.dataStore.clearFilter();
        this.dataStore.each(function(record){
            if(!record.get('deleted'))
                recordList[recordList.length] = record.get('id');
        });
        var result = {};
        if(recordList.length){
            result[this.fieldName+'[]'] = recordList;
        }else{
            result[this.fieldName]= '';
        }
        return result;
    },
    /**
     * Sets the read only state of this field.
     * @param Boolean readOnly
     * @return void
     */
    setReadOnly:function(readOnly){
        this.readOnly = readOnly;
        this.updateViewState();
    },
    updateViewState:function(){
        if(this.readOnly){
            this.addButton.disable();
        }else{
            this.addButton.enable();
        }
        this.showGrid();
    },
    showGrid:function(){

        if(this.gridRendered && this.readOnly == this.gridReadOnly){
            return;
        }

        var columns = [];

        if(this.statusColumn){
            columns.push({
                sortable: false,
                text: appLang.STATUS,
                dataIndex: 'published',
                width:60,
                align:'center',
                renderer:function(value, metaData, record, rowIndex, colIndex, store){
                    if(record.get('deleted')){
                        metaData.attr = 'style="background-color:#000000;white-space:normal;"';
                        return '<img src="'+app.wwwRoot+'i/system/trash.png" data-qtip="'+appLang.INSTANCE_DELETED+'" >';
                    }else{
                        return app.publishRenderer(value, metaData, record, rowIndex, colIndex, store);
                    }
                }
            });
        }

        columns.push({
            sortable: false,
            text: this.dataColumnTitle,
            flex:2,
            dataIndex: this.dataColumnIndex
        });

        if(!this.readOnly && this.sortColumn){
            columns.push(app.sortColumn());
        }

        if(this.deleteColumn){
            columns.push(app.deleteColumn());
        }

        this.dataGrid = Ext.create('Ext.grid.Panel',{
            store:this.dataStore,
            frame: false,
            loadMask:true,
            columnLines: true,
            scrollable:true,
            enableHdMenu:false,
            columns:columns
        });

        this.removeAll();
        this.add(this.dataGrid);
        this.gridRendered = true;
        this.gridReadOnly = this.readOnly;
    },
    destroy: function () {
        this.dataStore.destroy();
        this.addButton.destroy();
        this.addAllButton.destroy();
        this.callParent(arguments);
    }
});