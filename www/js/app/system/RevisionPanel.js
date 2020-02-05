Ext.define('app.revisionModel', {
    extend: 'Ext.data.Model',
    fields: [
        {name: 'version', type: 'integer'},
        {name: 'id', type: 'integer'},
        {name: 'date', type: 'date', dateFormat: 'Y-m-d H:i:s'},
        {name: 'user_name', type: 'string'}
    ]
});
/**
 * Revision panel component
 * @author Kirill Egorov
 * @extend Ext.Panel
 *
 * @event dataSaved
 * @param record
 *
 */
Ext.define('app.revisionPanel', {
    extend: 'Ext.panel.Panel',
    /**
     * @property {Ext.grid.Panel}
     */
    dataGrid: null,
    /**
     * @property {Ext.data.Store}
     */
    dataStore: null,
    /**
     * Data Item Id
     * @property integer
     */
    dataId: null,
    /**
     * Data item class
     * @property string
     */
    objectName: null,

    constructor: function (config) {

        config = Ext.apply({
            layout: 'fit',
            frame: false,
            border: false
        }, config || {});

        this.callParent(arguments);
    },
    initComponent: function () {

        this.dataStore = Ext.create('Ext.data.Store', {
            model: 'app.revisionModel',
            proxy: {
                type: 'ajax',
                url: app.admin + app.delimiter + 'vcs' + app.delimiter + 'list',
                reader: {
                    type: 'json',
                    rootProperty: 'data',
                    totalProperty: 'count',
                    idProperty: 'id'
                },
                startParam: 'pager[start]',
                limitParam: 'pager[limit]',
                sortParam: 'pager[sort]',
                directionParam: 'pager[dir]',
                extraParams: {
                    'filter[record_id]': this.dataId,
                    'object': this.objectName
                },
                simpleSortMode: true
            },
            pageSize: 10,
            remoteSort: true,
            autoLoad: this.dataId ? true : false,
            sorters: [{
                property: 'version',
                direction: 'DESC'
            }]
        });

        this.dataGrid = Ext.create('Ext.grid.Panel', {
            store: this.dataStore,
            viewConfig: {
                stripeRows: true
            },
            frame: false,
            loadMask: true,
            columnLines: true,
            scrollable: true,
            columns: [{
                xtype: 'actioncolumn',
                width: 30,
                colid: 'left',
                tooltip: appLang.LOAD_VERSION,
                iconCls: 'leftIcon',
                scope: this,
                handler: function (grid, rowIndex, colIndex) {
                    this.fireEvent('dataSelected', grid.getStore().getAt(rowIndex));
                }
            },
                {
                    text: appLang.VERS,
                    dataIndex: 'version',
                    width: 50,
                    colid: 'version',
                    sortable: true
                }, {
                    text: appLang.DATE,
                    dataIndex: 'date',
                    sortable: true,
                    width: 100,
                    xtype: 'datecolumn',
                    format: 'M d, Y H:i',
                    colid: 'date'
                }, {
                    text: appLang.USER,
                    colid: 'user_name',
                    dataIndex: 'user_name',
                    flex: 1,
                    sortable: true
                }
            ],
            bbar: Ext.create('Ext.PagingToolbar', {
                store: this.dataStore,
                displayInfo: true,
                displayMsg: appLang.DISPLAYING_RECORDS + ' {0} - {1} ' + appLang.OF + ' {2}',
                emptyMsg: appLang.NO_RECORDS_TO_DISPLAY
            })
        });

        this.dataGrid.on('itemdblclick', function (view, record, item, index, e, options) {
            this.fireEvent('dataSelected', record);
        }, this);

        this.dataGrid.on('cellclick', function (grid, cell, columnIndex, record, node, rowIndex, evt) {
            var column = grid.getHeaderCt().getHeaderAtIndex(columnIndex).colid;
            if (column == 'left') {
                this.fireEvent('dataSelected', record);
            }
        }, this);

        this.items = [this.dataGrid];

        this.callParent(arguments);
    },
    setRecordId: function (recordId) {
        this.dataStore.proxy.setExtraParam('filter[record_id]', recordId);
    },

    storeLoad: function () {
        this.dataStore.removeAll();
        this.dataStore.load();
    },
    destroy: function () {
        this.dataStore.destroy();
        this.dataGrid.destroy();
        this.callParent(arguments);
    }
});