Ext.ns('app.objectLink');
/**
 *
 * @event completeEdit
 *
 *
 * @event change
 * @param fld
 *
 */
Ext.define('app.objectLink.Field', {
    extend: 'Ext.form.FieldContainer',
    alias: 'widget.objectlinkfield',
    dataField: null,
    controllerUrl: '?',
    objectName: '',
    value: "",
    name: '',
    fieldLabel: '',
    layout: 'fit',
    readOnly: false,
    allowBlank: true,
    valueType:'integer',
    /**
     * Extra params for requests
     * @property {Object}
     */
    extraParams: null,
    ajaxActions: {
        title: 'otitle',
        list: 'linkedlist'
    },
    constructor: function (config) {
        config = Ext.apply({
            extraParams: {}
        }, config || {});
        this.callParent(arguments);
    },

    updateObjectTitle: true,

    initComponent: function () {

        var me = this;
        this.dataField = Ext.create('Ext.form.field.Hidden', {
            anchor: "100%",
            readOnly: true,
            name: this.name,
            allowBlank: this.allowBlank
        });

        this.dataField.on('change', me.getObjectTitle, me);
        this.dataField.on('focus', me.showSelectionWindow, me);

        this.dataFieldLabel = Ext.create('Ext.form.field.Text', {
            anchor: "100%",
            flex: 1,
            value: "...",
            editable: false,
            submitValue: false,
            //	cls:'d_objectLink_input',
            triggers: {
                select: {
                    cls: 'x-form-search-trigger',
                    handler: me.showSelectionWindow,
                    tooltip: appLang.SELECT,
                    scope: me
                },
                clear: {
                    cls: 'x-form-clear-trigger',
                    tooltip: appLang.RESET,
                    handler: function () {
                        me.setValue('');
                    },
                    scope: me
                }
            }
        });
        this.items = [this.dataFieldLabel, this.dataField];

        this.callParent();

        this.on('disable', function () {
            this.updateViewState();
        }, this);

        this.on('enable', function () {
            this.updateViewState();
        }, this);

        this.updateViewState();
    },
    isValid: function () {
        return this.dataField.isValid();
    },
    markInvalid: function () {
        this.dataFieldLabel.markInvalid();
    },
    showSelectionWindow: function () {

        if (this.readOnly || this.disabled) {
            return false;
        }

        var win = Ext.create('app.objectLink.SelectWindow', {
            width: 600,
            height: 500,
            selectMode: true,
            objectName: this.objectName,
            controllerUrl: this.controllerUrl + this.ajaxActions.list,
            title: this.fieldLabel,
            valueType:this.valueType,
            extraParams: this.extraParams
        });

        win.on('itemSelected', function (record) {
            this.setRawData(record.get('id'), record.get('title'));
            this.fireEvent('completeEdit');
            win.close();
        }, this);

        win.show();
        app.checkSize(win);
    },
    setValue: function (value) {
        this.updateObjectTitle = true;
        this.dataField.setValue(value);
        this.fireEvent('change', this);
    },
    getValue: function () {
        return this.dataField.getValue();
    },
    reset: function () {
        this.updateObjectTitle = true;
        this.dataField.reset();
        this.dataFieldLabel.reset();
        this.fireEvent('change', this);
    },
    setRawData: function (id, title) {
        this.dataField.setValue(id);
        this.dataFieldLabel.setValue(title);
        this.updateObjectTitle = false;
        this.fireEvent('change', this);
    },
    setObjectTitle: function (title) {
        this.dataFieldLabel.setValue(title);
        this.updateLayout();
    },
    getObjectTitle: function () {
        var me = this;
        var curValue = me.getValue();

        if (curValue == "" || curValue == 0) {
            me.dataFieldLabel.setValue('...');
            return;
        }

        if (!this.updateObjectTitle) {
            return;
        }

        me.dataFieldLabel.setValue(appLang.LOADING);

        Ext.Ajax.request({
            url: this.controllerUrl + this.ajaxActions.title,
            method: 'post',
            params: Ext.apply({
                object: this.objectName,
                id: curValue
            }, this.extraParams),
            scope: this,
            success: function (response, request) {
                response = Ext.JSON.decode(response.responseText);
                if (!response.success) {
                    Ext.Msg.alert(appLang.MESSAGE, response.msg);
                } else {
                    me.dataFieldLabel.setValue(response.data.title);
                    me.updateLayout();
                }
            },
            failure: function () {
                me.dataFieldLabel.setText('');
                app.ajaxFailure(arguments);
            }
        });
    },
    /**
     * Set request param
     * @param string name
     * @param string value
     * @return void
     */
    setExtraParam: function (name, value) {
        this.extraParams[name] = value;
    },
    /**
     * Sets the read only state of this field.
     * @param Boolean readOnly
     * @return void
     */
    setReadOnly: function (readOnly) {
        this.readOnly = readOnly;
        this.updateViewState();
    },
    updateViewState: function () {
        if (this.disabled) {
            this.dataFieldLabel.getTrigger('select').hide();
            this.dataFieldLabel.getTrigger('clear').hide();
        }
        else {
            if (this.readOnly) {
                this.dataFieldLabel.getTrigger('select').hide();
                this.dataFieldLabel.getTrigger('clear').hide();
            } else {
                this.dataFieldLabel.getTrigger('select').show();
                if (this.allowBlank) {
                    this.dataFieldLabel.getTrigger('clear').show();
                }
            }
        }
    },
    getObjectLabelValue: function(){
        return this.dataFieldLabel.getValue();
    },
    destroy: function () {
        this.dataField.destroy();
        this.dataFieldLabel.destroy();
        this.callParent(arguments);
    }
});


Ext.define('app.objectLink.SelectWindow', {
    extend: 'app.selectWindow',
    controllerUrl: '?',
    objectName: '',
    fieldName: '',
    singleSelect: true,
    valueType:'integer',
    /**
     * Extra params for requests
     * @property {Object}
     */
    extraParams: null,

    constructor: function (config) {
        config = Ext.apply({
            extraParams: {}
        }, config || {});
        this.callParent(arguments);
    },

    initComponent: function () {

        this.dataStore = Ext.create('Ext.data.Store', {
            fields: [
                {name: 'id', type: this.valueType},
                {name: 'title', type: 'string'},
                {name: 'published', type: 'boolean'},
                {name: 'deleted', type: 'boolean'}
            ],
            proxy: {
                type: 'ajax',
                url: this.controllerUrl,
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
                extraParams: Ext.apply({
                    'object': this.objectName
                }, this.extraParams),
                simpleSortMode: true
            },
            autoLoad: true,
            pageSize: 25,
            remoteSort: true
        });

        this.searchField = Ext.create('SearchPanel', {
            store: this.dataStore,
            local: false,
            fieldNames: ['title']
        });


        this.dataPanel = Ext.create('Ext.grid.Panel', {
            viewConfig: {
                stripeRows: true
            },
            frame: false,
            loadMask: true,
            columnLines: true,
            scrollable: true,
            store: this.dataStore,
            tbar: [
                '->', this.searchField
            ],
            bbar: Ext.create("Ext.PagingToolbar", {
                store: this.dataStore,
                displayInfo: true,
                displayMsg: appLang.DISPLAYING_RECORDS + " {0} - {1} " + appLang.OF + " {2}",
                emptyMsg: appLang.NO_RECORDS_TO_DISPLAY
            }),
            columns: [
                {
                    dataIndex: 'published',
                    text: appLang.STATUS,
                    width: 50,
                    align: 'center',
                    renderer: function (value, metaData, record, rowIndex, colIndex, store) {
                        if (record.get('deleted')) {
                            metaData.attr = 'style="background-color:#000000;white-space:normal;"';
                            return '<img src="' + app.wwwRoot + 'i/system/trash.png" data-qtip="' + appLang.INSTANCE_DELETED + '" >';
                        } else {
                            return app.publishRenderer(value, metaData, record, rowIndex, colIndex, store);
                        }
                    }
                },
                {
                    dataIndex: 'title',
                    text: appLang.TITLE,
                    flex: 1
                }
            ]
        });

        this.callParent(arguments);
    },
    /**
     * Set request param
     * @param string name
     * @param string value
     * @return void
     */
    setExtraParam: function (name, value) {
        this.extraParams[name] = value;
    },
    /**
     * Get data storage
     * @return {Ext.data.Store}
     */
    getDataStore: function(){
        return this.dataStore;
    }
});

Ext.define('app.objectLink.Panel', {
    extend: 'app.relatedGridPanel',
    alias: 'widget.objectlinkpanel',
    name: '',
    objectName: '',
    valueType:'integer',
    controllerUrl: '',

    initComponent: function () {
        this.fieldName = this.name;
        this.callParent(arguments);
        this.on('addItemCall', this.showSelectWindow, this);
        this.on('addAllItemCall', this.selectAllItems, this);
    },
    showSelectWindow: function () {
        var win = Ext.create('app.objectLink.SelectWindow', {
            width: 600,
            height: 500,
            selectMode: true,
            objectName: this.objectName,
            valueType:this.valueType,
            controllerUrl: this.controllerUrl + 'linkedlist',
            title: this.fieldLabel,
            extraParams: this.extraParams
        });
        win.on('itemSelected', function (record) {
            this.addRecord(record);
            this.fireEvent('completeEdit');
        }, this);
        win.show();
        app.checkSize(win);
    },
    selectAllItems: function(){
        var win = Ext.create('app.objectLink.SelectWindow', {
            width: 600,
            height: 500,
            selectMode: true,
            objectName: this.objectName,
            valueType:this.valueType,
            controllerUrl: this.controllerUrl + 'linkedlist',
            title: this.fieldLabel,
            extraParams: this.extraParams
        });
        var store = win.getDataStore();
        store.setPageSize(0);
        store.load({
            scope:this,
            callback:function(records, operation, success) {
                this.addRecords(records);
                this.fireEvent('completeEdit');
            }
        });
    },
    /**
     * Set request param
     * @param {string} name
     * @param {string} value
     * @return void
     */
    setExtraParam: function (name, value) {
        this.extraParams[name] = value;
    }
});
