/**
 * History panel component
 * @author Kirill Egorov
 * @extend Ext.Panel
 */
Ext.define('app.historyModel', {
    extend: 'Ext.data.Model',
    fields: [
        {name: 'id', type: 'integer'},
        {name: 'date', type: 'date', dateFormat: 'Y-m-d H:i:s'},
        {name: 'user_name', type: 'string'},
        {name: 'type', type: 'string'}
    ]
});

Ext.define('app.historyPanel', {
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
     * DB table
     * @property string
     */
    dataTable: null,
    /**
     * Num records to show on page
     * @property
     */
    rowsOnPage: 10,
    /**
     * Data object name
     * @proprty string
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
            model: 'app.historyModel',
            proxy: {
                type: 'ajax',
                url: app.admin + app.delimiter + 'history' + app.delimiter + 'list',
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
            pageSize: this.rowsOnPage,
            remoteSort: true,
            autoLoad: this.dataId ? true : false,
            sorters: [{
                property: 'date',
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
            columns: [
                {
                    header: appLang.DATE,
                    dataIndex: 'date',
                    sortable: true,
                    width: 100,
                    xtype: 'datecolumn',
                    format: 'M d, Y H:i'
                }, {
                    header: appLang.ACTION,
                    dataIndex: 'type',
                    width: 60,
                    sortable: true
                }, {
                    header: appLang.USER,
                    dataIndex: 'user_name',
                    sortable: true,
                    flex: 1
                }
            ],
            bbar: Ext.create('Ext.PagingToolbar', {
                store: this.dataStore,
                displayInfo: true,
                displayMsg: appLang.DISPLAYING_RECORDS + ' {0} - {1} ' + appLang.OF + ' {2}',
                emptyMsg: appLang.NO_RECORDS_TO_DISPLAY
            })
        });

        this.items = [this.dataGrid];

        app.historyPanel.superclass.initComponent.call(this);
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
