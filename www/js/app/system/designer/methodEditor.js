/**
 * @event methodsUpdated
 */
Ext.define('designer.methodsEditor', {
    extend: 'Ext.grid.Panel',
    scrollable: true,
    controllerUrl: null,
    searchField: null,
    columnLines: true,
    viewConfig: {stripeRows: true, enableTextSelection: true},

    initComponent: function () {

        if (!this.controllerUrl.length) {
            this.controllerUrl = app.createUrl([designer.controllerUrl, 'methods', '']);
        }

        this.store = Ext.create('Ext.data.Store', {
            fields: [
                {name: 'enabled', type: 'boolean'},
                {name: 'object', type: 'string'},
                {name: 'method', type: 'string'},
                {name: 'params', type: 'string'},
                {name: 'has_code', type: 'boolean'},
                {name: 'description', type: 'string'}
            ],
            proxy: {
                type: 'ajax',
                url: this.controllerUrl + 'list',
                reader: {
                    type: 'json',
                    rootProperty: 'data',
                    idProperty: 'id'
                },
                simpleSortMode: true
            },
            groupField: 'object',
            remoteSort: false,
            autoLoad: false,
            sorters: [{
                property: 'object',
                direction: 'DESC'
            }, {
                property: 'method',
                direction: 'DESC'
            }]
        });

        this.searchField = Ext.create('SearchPanel', {
            store: this.store,
            local: true,
            fieldNames: ['object', 'method']
        });

        this.tbar = [
            {
                iconCls: 'refreshIcon',
                tooltip: desLang.refresh,
                scope: this,
                handler: function () {
                    this.store.load();
                }
            },
            this.searchField
        ];

        this.columns = [
            {
                xtype: 'actioncolumn',
                width: 20,
                items: [
                    {
                        iconCls: 'editIcon',
                        handler: function (grid, rowIndex, colIndex) {
                            var rec = grid.getStore().getAt(rowIndex);
                            this.editMethod(rec);
                        },
                        scope: this
                    }
                ]
            }, {
                text: desLang.object,
                dataIndex: 'object',
                width: 150
            }, {
                text: desLang.method,
                dataIndex: 'method',
                width: 150
            }, {
                text: desLang.params,
                dataIndex: 'params',
                flex: 1
            }, {
                text: desLang.description,
                dataIndex: 'description',
                flex: 1
            }, {
                text: desLang.active,
                dataIndex: 'enabled',
                align: 'center',
                width: 40,
                renderer: app.checkboxRenderer
            }, {
                xtype: 'actioncolumn',
                width: 20,
                items: [
                    {
                        iconCls: 'deleteIcon',
                        handler: function (grid, rowIndex, colIndex) {
                            var rec = grid.getStore().getAt(rowIndex);
                            this.removeMethod(rec);
                        },
                        scope: this
                    }
                ]
            }
        ];

        this.features = [Ext.create('Ext.grid.feature.Grouping', {
            groupHeaderTpl: '{name} ({rows.length})',
            startCollapsed: 0,
            enableGroupingMenu: 1,
            hideGroupedHeader: 0
        })];

        this.on('itemdblclick', function (view, record, number, event, options) {
            this.editMethod(record);
        }, this);


        this.callParent();
    },
    /**
     * Remove object method
     * @param {Ext.data.Record}
     */
    removeMethod: function (record) {
        Ext.Ajax.request({
            url: this.controllerUrl + 'removemethod',
            method: 'post',
            scope: this,
            params: {
                object: record.get('object'),
                method: record.get('method')
            },
            success: function (response, request) {
                response = Ext.JSON.decode(response.responseText);
                if (!response.success) {
                    Ext.Msg.alert(appLang.MESSAGE, response.msg);
                    return;
                }
                designer.msg(appLang.MESSAGE, desLang.msg_methodRemoved);
                this.getStore().remove(record);
                this.getStore().commitChanges();
                this.fireEvent('methodsUpdated');
            },
            failure: function () {
                Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
            }
        });
    },
    /**
     * Update object method
     * @param {Ext.data.Record}
     */
    editMethod: function (record) {
        Ext.create('designer.methodEditorWindow', {
            controllerUrl: this.controllerUrl,
            objectName: record.get('object'),
            methodName: record.get('method'),
            paramsString: record.get('params'),
            modal: false,
            listeners: {
                'codeSaved': {
                    fn: function () {
                        this.getStore().load();
                        this.fireEvent('methodsUpdated');
                    },
                    scope: this
                }
            }
        }).show();
    },
    destroy: function () {
        this.store.destroy();
        this.searchField.destroy();
        this.callParent(arguments);
    }
});


/**
 *
 * @event codeSaved
 */
Ext.define('designer.methodEditorWindow', {
    extend: 'Ext.Window',
    modal: false,
    width: 800,
    height: 600,
    y: 20,
    layout: {
        type: 'vbox',
        align: 'stretch',
        pack: 'start'
    },
    //autoRender:true,
    maximizable: true,
    extraParams: null,

    closeAction: 'destroy',

    controllerUrl: '',
    objectName: '',
    methodName: '',
    paramsString: '',
    editor: null,

    constructor: function () {
        this.extraParams = {};
        this.callParent(arguments);
    },

    initComponent: function () {

        this.extraParams['object'] = this.objectName;
        this.extraParams['method'] = this.methodName;

        this.title = this.objectName + '.' + this.methodName;

        this.saveButton = Ext.create('Ext.Button', {
            disabled: true,
            text: desLang.save,
            scope: this,
            handler: this.saveMethod
        });

        this.cancelButton = Ext.create('Ext.Button', {
            text: desLang.close,
            scope: this,
            handler: this.close
        });

        this.buttons = [this.saveButton, this.cancelButton];

        this.centerPanel = Ext.create('Ext.Container', {
            region: 'center',
            layout: 'fit'
        });

        this.dataForm = Ext.create('Ext.form.Panel', {
            bodyPadding: 5,
            bodyCls: 'formBody',
            border: false,
            bosyPadding: 5,
            items: [
                {
                    xtype: 'textarea',
                    labelAlign: 'top',
                    name: 'description',
                    anchor: '100%',
                    fieldLabel: desLang.description
                }, {
                    xtype: 'fieldcontainer',
                    layout: {
                        type: 'hbox',
                        pack: 'start',
                        align: 'stretch'
                    },
                    height: 22,
                    items: [
                        {
                            xtype: 'textfield',
                            name: 'method_name',
                            flex: 1,
                            fieldStyle: {
                                border: 'none',
                                //   textAlign:'right',
                                background: 'none',
                                backgroundColor: '#F4F4F4'
                                //borderBottom:'1px solid #000000'
                            }
                        }, {
                            xtype: 'displayfield',
                            value: ' : <span style="color:#7F0055;font-weight:bold;">function</span>(  '
                        }, {
                            xtype: 'textfield',
                            name: 'params',
                            flex: 2,
                            fieldStyle: {
                                border: 'none',
                                //   textAlign:'left',
                                background: 'none',
                                backgroundColor: '#F4F4F4',
                                //borderBottom:'1px solid #000000',
                                color: '#5C3BFB'
                            }
                        }, {
                            xtype: 'displayfield',
                            value: '  )'
                        }
                    ]
                }
            ]
        });

        this.items = [this.dataForm];

        this.callParent();

        this.on('show', function () {
            this.loadMethodData();
            app.checkSize(this);
            Ext.WindowMgr.register(this);
            Ext.WindowMgr.bringToFront(this);
        }, this);
    },
    /**
     * Request method data
     */
    loadMethodData: function () {
        var me = this;
        Ext.Ajax.request({
            url: this.controllerUrl + 'methoddata',
            method: 'post',
            scope: this,
            params: this.extraParams,
            success: function (response, request) {
                response = Ext.JSON.decode(response.responseText);
                if (!response.success) {
                    Ext.Msg.alert(appLang.MESSAGE, response.msg);
                    return;
                }
                me.editor = Ext.create('designer.codeEditor', {
                    readOnly: false,
                    showSaveBtn: false,
                    flex: 1,
                    sourceCode: response.data['code'],
                    headerText: '{',
                    footerText: '}',
                    extraKeys: {
                        "Ctrl-Space": function (cm) {
                            CodeMirror.simpleHint(cm, CodeMirror.javascriptHint);
                        },
                        "Ctrl-S": function (cm) {
                            me.saveMethod();
                        },
                        "Ctrl-Z": function (cm) {
                            me.editor.undoAction();
                        },
                        "Ctrl-Y": function (cm) {
                            me.editor.redoAction();
                        },
                        "Shift-Ctrl-Z": function (cm) {
                            me.editor.redoAction();
                        }
                    }
                });

                var form = this.dataForm.getForm();
                form.findField('description').setValue(response.data['description']);
                form.findField('method_name').setValue(response.data['name']);
                form.findField('params').setValue(response.data['paramsLine']);
                this.add(me.editor);
                this.saveButton.enable();
            },
            failure: function () {
                Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
            }
        });
    },
    /**
     * Save method data
     */
    saveMethod: function () {
        var form = this.dataForm.getForm();
        var code = this.editor.getValue();

        var params = Ext.clone(this.extraParams);
        params['code'] = code;

        form.submit({
            clientValidation: true,
            waitMsg: appLang.SAVING,
            method: 'post',
            scope: this,
            params: params,
            url: this.controllerUrl + 'update',
            success: function (form, action) {
                if (!action.result.success) {
                    Ext.Msg.alert(appLang.MESSAGE, action.result.msg);
                } else {
                    designer.msg(appLang.MESSAGE, desLang.msg_codeSaved);
                    this.fireEvent('codeSaved');
                    // update method name
                    this.extraParams['method'] = form.findField('method_name').getValue();
                    this.methodName = this.extraParams['method'];
                }
            },
            failure: app.formFailure
        });
    },
    destroy: function () {
        this.centerPanel.destroy();
        this.dataForm.destroy();
        this.callParent(arguments);
    }
});