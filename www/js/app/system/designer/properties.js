Ext.ns('designer.properties');

Ext.define('designer.properties.idStringModel',{
    extend: 'Ext.data.Model',
    idProperty:'id',
    fields:[
        {name:'id', type:'string'}
    ]
});

Ext.define('designer.properties.nameTitleModel',{
    extend: 'Ext.data.Model',
    idProperty:'name',
    fields:[
        {name:'name', type:'string'},
        {name:'title', type:'string'}
    ]
});

/**
 * Properties editor base
 *
 * @event dataSaved
 * @param string recordId
 * @param mixed value
 *
 *
 * @event eventsUpdated
 *
 * @event afterLoad
 * @param response
 *
 * @event objectsUpdated
 *
 */
Ext.define('designer.properties.Panel', {
    extend: 'Ext.Panel',
    layout: 'fit',
    border: false,

    searchPanel: null,

    dataStore: null,
    dataGrid: null,
    controllerUrl: '',
    scrollable: false,

    controllerUr: '',
    eventsControllerUrl: '',

    eventsPanel: null,
    methodsPanel: null,
    /**
     * Current object name
     * @var string
     */
    objectName: null,

    tabs: null,

    showEvents: true,
    showMethods: false,

    extraParams: null,

    alignData: [['left'], ['center'], ['right']],
    labelAlignData: [['left'], ['top'], ['right']],
    iconAlignData:[['top'],['right'],['bottom'],['left']],
    regionData: [['center'], ['west'], ['north'], ['east'], ['south']],
    layoutData: [['Auto'], ['border'], ['card'], ['fit'], ['hbox'], ['vbox'], ['anchor'], ['center'], ['absolute']], //['table']
    dockData: [['top'], ['right'], ['left'], ['bottom']],


    mainConfigTitle: desLang.properties,

    autoLoadData: true,

    methodsSearch: false,
    useTabs: true,

    constructor: function () {
        this.extraParams = {};
        this.sourceConfig = {};
        this.callParent(arguments);
    },

    initComponent: function () {
        var me = this;

        this.extraParams.object = this.objectName;

        var menuStore = app.designer.getMenuStore();

        this.objectNames = {};

        this.sourceConfig = Ext.apply({

            'region': {
                editor: Ext.create('Ext.form.ComboBox', {
                    selectOnFocus: true,
                    editable: true,
                    triggerAction: 'all',
                    anchor: '100%',
                    queryMode: 'local',
                    store: Ext.create('Ext.data.ArrayStore', {
                        model:'designer.properties.idStringModel',
                        data: this.regionData
                    }),
                    valueField: 'id',
                    displayField: 'id',
                    allowBlank: true,
                    forceSelection: false
                })
            },

            'layout': {
                editor: Ext.create('Ext.form.ComboBox', {
                    selectOnFocus: true,
                    editable: true,
                    triggerAction: 'all',
                    anchor: '100%',
                    queryMode: 'local',
                    store: Ext.create('Ext.data.ArrayStore', {
                        model:'designer.properties.idStringModel',
                        data: this.layoutData
                    }),
                    valueField: 'id',
                    displayField: 'id',
                    allowBlank: true
                })
            },
            'dock': {
                editor: Ext.create('Ext.form.ComboBox', {
                    selectOnFocus: true,
                    editable: true,
                    triggerAction: 'all',
                    anchor: '100%',
                    queryMode: 'local',
                    store: Ext.create('Ext.data.ArrayStore', {
                        model:'designer.properties.idStringModel',
                        data: this.dockData
                    }),
                    valueField: 'id',
                    displayField: 'id',
                    allowBlank: true
                })
            },
            'align': {
                editor: Ext.create('Ext.form.ComboBox', {
                    selectOnFocus: true,
                    editable: true,
                    triggerAction: 'all',
                    anchor: '100%',
                    queryMode: 'local',
                    store: Ext.create('Ext.data.ArrayStore', {
                        model:'designer.properties.idStringModel',
                        data: this.alignData
                    }),
                    valueField: 'id',
                    displayField: 'id',
                    allowBlank: true
                })
            },
            'titleAlign': {
                editor: Ext.create('Ext.form.ComboBox', {
                    selectOnFocus: true,
                    editable: true,
                    triggerAction: 'all',
                    anchor: '100%',
                    queryMode: 'local',
                    store: Ext.create('Ext.data.ArrayStore', {
                        model:'designer.properties.idStringModel',
                        data: this.alignData
                    }),
                    valueField: 'id',
                    displayField: 'id',
                    allowBlank: true
                })
            },
            'labelAlign': {
                editor: Ext.create('Ext.form.ComboBox', {
                    selectOnFocus: true,
                    editable: true,
                    triggerAction: 'all',
                    anchor: '100%',
                    queryMode: 'local',
                    store: Ext.create('Ext.data.ArrayStore', {
                        model:'designer.properties.idStringModel',
                        data: this.labelAlignData
                    }),
                    valueField: 'id',
                    displayField: 'id',
                    allowBlank: true
                })
            },
            'iconAlign': {
                editor: Ext.create('Ext.form.ComboBox', {
                    selectOnFocus: true,
                    editable: true,
                    triggerAction: 'all',
                    anchor: '100%',
                    queryMode: 'local',
                    store: Ext.create('Ext.data.ArrayStore', {
                        model:'designer.properties.idStringModel',
                        data: this.iconAlignData
                    }),
                    valueField: 'id',
                    displayField: 'id',
                    allowBlank: true
                })
            },
            // 'store': {
            //     editor: Ext.create('Ext.form.field.ComboBox', {
            //         typeAhead: true,
            //         triggerAction: 'all',
            //         selectOnTab: true,
            //         labelWidth: 80,
            //         forceSelection: false,
            //         queryMode: 'remote',
            //         displayField: 'title',
            //         valueField: 'id',
            //         store: storesStore
            //     })
            // },
            'store':{
                editor: Ext.create('Ext.form.field.Text', {
                    listeners: {
                        focus: {
                            fn: me.showStoreWindow,
                            scope: me
                        }
                    }
                }),
                renderer:function(v){return '...';}
            },

            // 'mapping':{
            //     editor:Ext.create('Ext.form.field.ComboBox',{
            //         typeAhead: true,
            //         triggerAction: 'all',
            //         selectOnTab: true,
            //         labelWidth:80,
            //         forceSelection:false,
            //         queryMode:'local',
            //         displayField:'title',
            //         valueField:'title',
            //         store:app.designer.getModelsStore()
            //     })
            // },
            'menu': {
                editor: Ext.create('Ext.form.field.ComboBox', {
                    typeAhead: true,
                    triggerAction: 'all',
                    selectOnTab: true,
                    labelWidth: 80,
                    forceSelection: false,
                    queryMode: 'remote',
                    displayField: 'title',
                    valueField: 'id',
                    store: menuStore
                })
            },
            'url': {
                editor: Ext.create('designer.urlField', {
                    controllerUrl: app.createUrl([designer.controllerUrl, 'url', 'actions', '']),
                    listeners: {
                        select: {
                            fn: function (url) {
                                me.dataGrid.setProperty('url' , url);
                            },
                            scope:me
                        }
                    }
                })
            },
            'icon': {
                editor: Ext.create('designer.iconField', {
                    controllerUrl: app.createUrl([designer.controllerUrl, 'url', '']),
                    listeners: {
                        select: {
                            fn: function (url) {
                                me.dataGrid.setProperty('icon' , url);
                            },
                            scope:me
                        }
                    }
                })
            },
            'controllerUrl': {
                editor: Ext.create('designer.urlField', {
                    onlyController: true,
                    controllerUrl: app.createUrl([designer.controllerUrl, 'url', 'actions', '']),
                    listeners: {
                        select: {
                            fn: function (url) {
                                me.dataGrid.setProperty('controllerUrl' , url);
                            },
                            scope:me
                        }
                    }
                })
            },
            'defaults': {
                editor: Ext.create('Ext.form.field.Text', {
                    listeners: {
                        focus: {
                            fn: function () {
                                me.showDefaultsWindow('defaults');
                            },
                            scope: me
                        }
                    }
                }),
                renderer:function(){
                    return '...';
                }
            },
            'extraParams': {
                editor: Ext.create('Ext.form.field.Text', {
                    listeners: {
                        focus: {
                            fn: function () {
                                me.showParamsWindow('extraParams');
                            },
                            scope: me
                        }
                    }
                }),
                renderer:function(){
                    return '...';
                }
            },
            'fieldDefaults': {
                editor: Ext.create('Ext.form.field.Text', {
                    listeners: {
                        focus: {
                            fn: function () {
                                me.showDefaultsWindow('fieldDefaults');
                            },
                            scope: me
                        }
                    }
                }),
                renderer:function(){
                    return '...';
                }
            },
            'isExtended':{
                editor: Ext.create('Ext.form.field.Display', {})
            },
            'dictionary': {
                editor: Ext.create('Ext.form.field.ComboBox', {
                    typeAhead: true,
                    triggerAction: 'all',
                    selectOnTab: true,
                    labelWidth: 80,
                    forceSelection: false,
                    queryMode: 'remote',
                    displayField: 'title',
                    valueField: 'id',
                    store: Ext.create('Ext.data.Store', {
                        model: 'app.comboStringModel',
                        proxy: {
                            type: 'ajax',
                            url: this.controllerUrl + 'listdictionaries',
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
                            property: 'title',
                            direction: 'DESC'
                        }]
                    })
                })
            },
            'config': {
                editor: Ext.create('Ext.form.field.Text', {
                    listeners: {
                        focus: {
                            fn: function () {
                                me.showDefaultsWindow('config');
                            },
                            scope: me
                        }
                    }
                })
            },
            'objectName': {
                editor: Ext.create('Ext.form.field.ComboBox', {
                    typeAhead: true,
                    triggerAction: 'all',
                    selectOnTab: true,
                    labelWidth: 80,
                    forceSelection: true,
                    queryMode: 'local',
                    displayField: 'title',
                    valueField: 'name',
                    store: Ext.create('Ext.data.Store', {
                        proxy: {
                            type: 'ajax',
                            url: app.createUrl([designer.controllerUrl, 'orm', 'list']),
                            reader: {
                                type: 'json',
                                idProperty: 'name',
                                rootProperty: 'data'
                            }
                        },
                        model:'designer.properties.nameTitleModel',
                        autoLoad: true,
                        listeners: {
                            scope: me,
                            load: function (store) {
                                me.objectNames = {};
                                store.each(function (record) {
                                    me.objectNames[record.get('name')] = record.get('title');
                                }, me);
                            }
                        }
                    }),
                    renderer:function(){
                        return '...';
                    }
                })
            }
        }, this.sourceConfig);

        this.searchPanel = Ext.create('SearchPanel', {
            fieldNames: ['name'],
            local: true,
            width: 130,
            hideLabel: true
        });

        this.showCodeBtn = Ext.create('Ext.Button', {
            scope: me,
            iconCls: 'jsIcon',
            handler: me.showCode,
            tooltip: desLang.showCode
        });

        this.dataGrid = Ext.create('Ext.grid.property.Grid', {
            border: false,
            region: 'center',
            split: true,
            scrollable: true,
            title: this.mainConfigTitle,
            tbar: [this.searchPanel, '->', this.showCodeBtn],
            sourceConfig: this.sourceConfig,
            //customEditors:this.customEditors,
            customRenderers: this.customRenderers,
            nameColumnWidth: 150,
            listeners: {
                propertychange: {
                    fn: this.onChange,
                    scope: this
                }
            },
            source: {}
        });

        this.searchPanel.store = this.dataGrid.getStore();

        var itemsList = [this.dataGrid];
        if (this.showEvents) {
            this.eventsPanel = Ext.create('designer.eventsPanel', {
                title: desLang.events,
                controllerUrl: this.eventsControllerUrl,
                objectName: this.objectName,
                extraParams: this.extraParams,
                autoLoadData: this.autoLoadData,
                listeners: {
                    'eventsUpdated': {
                        fn: function () {
                            this.fireEvent('eventsUpdated');
                        },
                        scope: this
                    }
                }
            });

            itemsList.push(this.eventsPanel);
        }

        if (this.showMethods) {
            this.methodsPanel = Ext.create('designer.methodsPanel', {
                title: desLang.methods,
                controllerUrl: this.methodsControllerUrl,
                objectName: this.objectName,
                extraParams: this.extraParams,
                autoLoadData: this.autoLoadData,
                listeners: {
                    'methodsUpdated': {
                        fn: function () {
                            this.fireEvent('methodsUpdated');
                        },
                        scope: this
                    }
                }
            });

            itemsList.push(this.methodsPanel);

            if (this.methodsSearch) {
                this.methodsPanel.setSearchText(this.methodsSearch);
                this.methodsSearch = false;
            }
        }

        if (this.useTabs) {
            this.tabs = Ext.create('Ext.tab.Panel', {
                items: itemsList
            });
            this.items = [this.tabs];
        } else {
            this.items = [this.dataGrid];
        }
        itemsList = null;
        this.callParent();

        /*
        this.on('scrollershow', function (scroller) {
            if (scroller && scroller.scrollEl) {
                scroller.clearManagedListeners();
                scroller.mon(scroller.scrollEl, 'scroll', scroller.onElScroll, scroller);
            }
        }, this);
        */

        if (this.autoLoadData) {
            this.loadProperties();
        }
    },
    /**
     * reload object events
     */
    refreshEvents: function () {
        if (this.showEvents) {
            this.eventsPanel.getStore().proxy.extraParams = this.extraParams;
            this.eventsPanel.getStore().load();
        }
    },
    /**
     * reload object methods
     */
    refreshMethods: function () {
        if (this.showMethods) {
            this.methodsPanel.getStore().proxy.extraParams = this.extraParams;
            this.methodsPanel.getStore().load();
        }
    },
    /**
     * Clear property grid
     */
    resetProperties: function () {
        this.dataGrid.getStore().removeAll();
    },
    /**
     * Load object properties
     */
    loadProperties: function () {
        this.loadRequest = Ext.Ajax.request({
            url: this.controllerUrl + 'list',
            method: 'post',
            scope: this,
            params: this.extraParams,
            success: function (response) {
                response = Ext.JSON.decode(response.responseText);
                if (response.success) {
                    if (!Ext.isEmpty(this.eventsPanel) && !Ext.isEmpty(this.methodsPanel)) {
                        if (!Ext.isEmpty(response.data.isExtended) && response.data.isExtended) {
                            this.eventsPanel.setCanEditLocalEvents(true);
                            this.methodsPanel.enable();
                        } else {
                            this.eventsPanel.setCanEditLocalEvents(false);
                            this.methodsPanel.disable();
                        }
                    }
                    this.dataGrid.setSource(response.data);
                    this.dataGrid.getStore().sort('name', 'ASC');
                    this.fireEvent('afterLoad', response);
                } else {
                    this.dataGrid.setSource({});
                }
            },
            failure: function (response) {
                if(response && !response.aborted){
                    Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
                }
            }
        });
    },
    /**
     * Add additional query params
     * @param {Object} params
     */
    setExtraParams: function (params) {
        this.extraParams = Ext.apply(this.extraParams, params);
    },
    /**
     * Get additional query param
     * @param name
     * @returns mixed
     */
    getExtraParam: function (name) {
        return this.extraParams[name];
    },
    /**
     * Get properties filter text
     */
    getSearchText: function () {
        if (!Ext.isEmpty(this.searchPanel) && !Ext.isEmpty(this.searchPanel.searchField)) {
            return this.searchPanel.searchField.getValue();
        }
        return false;
    },
    /**
     * Set search filter for properties
     * @param {string} text
     */
    setSearchText: function (text) {
        if (this.searchPanel) {
            this.searchPanel.setValue(text);
        }
    },
    /**
     * Set search filter for events
     * @param {string} text
     */
    setEventsSearchText: function (text) {
        if (this.eventsPanel) {
            this.eventsPanel.setSearchText(text);
        }
    },
    /**
     * Set search filter fpr methods
     * @param {string} text
     */
    setMethodsSearchText: function (text) {
        if (this.methodsPanel) {
            this.methodsPanel.setSearchText(text);
        } else {
            this.methodsSearch = text;
        }
    },
    /**
     * Set search filter for events
     * @return string
     */
    getEventsSearchText: function () {
        if (!Ext.isEmpty(this.eventsPanel)) {
            return this.eventsPanel.getSearchText();
        }
        return '';
    },
    /**
     * Set search filter for methods
     * @return string
     */
    getMethodsSearchText: function () {
        if (!Ext.isEmpty(this.methodsPanel)) {
            return this.methodsPanel.getSearchText();
        }
        return '';
    },
    onChange: function (source, recordId, value, oldValue, eOpts) {
        if (value === oldValue){
            return;
        }

        var params = Ext.apply({name: recordId, value: value}, this.extraParams);

        Ext.Ajax.request({
            url: this.controllerUrl + 'setproperty',
            method: 'post',
            scope: this,
            params: params,
            success: function (response, request) {
                response = Ext.JSON.decode(response.responseText);
                if (!response.success) {
                    this.dataGrid.suspendEvents();
                    this.dataGrid.setProperty(recordId, oldValue);
                    this.dataGrid.resumeEvents();
                    var msg = ' <br>';
                    if (!Ext.isEmpty(response.msg)) {
                        msg += response.msg;
                    }
                    Ext.Msg.alert(appLang.MESSAGE, desLang.cantSaveProperty + ' "' + recordId + '".' + msg);
                } else {
                    this.fireEvent('dataSaved', recordId, value);
                    designer.msg(desLang.msg, desLang.msg_propertySaved);
                   /* if (recordId == 'isExtended') {
                        if (value) {
                            this.eventsPanel.setCanEditLocalEvents(true);
                            this.methodsPanel.enable();
                        } else {
                            this.eventsPanel.setCanEditLocalEvents(false);
                            this.methodsPanel.disable();
                        }
                    }
                    */
                }
            },
            failure: function () {
                Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
            }
        });
    },
    resetSearchField: function () {
        this.searchPanel.searchField.reset();
    },
    /**
     * Show source code
     */
    showCode: function () {
        Ext.Ajax.request({
            url: app.createUrl([designer.controllerUrl, 'code', 'objectcode']),
            method: 'post',
            scope: this,
            params: {
                object: this.objectName
            },
            success: function (response) {
                response = Ext.JSON.decode(response.responseText);
                if (!response.success) {
                    Ext.Msg.alert(appLang.MESSAGE, response.msg);
                    return;
                }

                var editor = Ext.create('designer.codeEditor', {
                    sourceCode: response.data,
                    readOnly: true
                });

                Ext.create('Ext.Window', {
                    title: desLang.sourceCodeFor + ' "' + this.objectName + '"',
                    layout: 'fit',
                    width: 750,
                    height: 600,
                    modal: true,
                    maximizable: true,
                    items: [editor]
                }).show();
            },
            failure: function () {
                Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
            }
        });
    },
    showDefaultsWindow: function (property) {
        var me = this;
        var source = this.dataGrid.getSource();
        var data = [];
        if (!Ext.isEmpty(source[property])) {
            var tmp = Ext.JSON.decode(source[property]);

            for (var i in tmp) {
                if (typeof tmp[i] != 'function') {
                    data.push({'key': i, 'value': tmp[i]});
                }
            }

        }
        var win = Ext.create('designer.defaultsWindow', {
            title: property,
            initialData: data
        });

        win.on('dataChanged', function (value) {
            me.dataGrid.setProperty(property, value);
        }, me);

        Ext.defer(function () {
            win.show().toFront();
        }, 50);
    },
    showParamsWindow: function (property) {
        var me = this;
        var source = this.dataGrid.getSource();
        var data = [];

        if (!Ext.isEmpty(source[property])) {
            var tmp = Ext.JSON.decode(source[property]);
            for (var i in tmp) {
                if (typeof tmp[i] != 'function') {
                    data.push({'key': i, 'value': tmp[i]});
                }
            }
        }

        var win = Ext.create('designer.paramsWindow', {
            title: property,
            initialData: data
        });

        win.on('dataChanged', function (value) {
            me.dataGrid.setProperty(property, value);
        }, me);

        Ext.defer(function () {
            win.show().toFront();
        }, 50);
    },
    showStoreWindow:function(){
        var listStore = app.designer.createStoresList(false);
        var instanceStore = app.designer.createStoresList(true);

        var win = Ext.create('designer.store.PropertyWindow',{
            title:desLang.store,
            modal:true,
            objectName : this.objectName,
            columnId: this.extraParams.id,
            controllerUrl:this.controllerUrl,
            storesStore:listStore,
            instancesStore:instanceStore
        });
        Ext.defer(function () {
            win.show().toFront();
        }, 50);
    },
    destroy:function(){
        this.showCodeBtn.destroy();
        if(this.loadRequest && this.loadRequest.destroy){
            this.loadRequest.abort();
            this.loadRequest.destroy();
        }
        this.dataGrid.clearListeners();
        this.dataGrid.destroy();
        this.searchPanel.destroy();
        if(this.methodsPanel){
            this.methodsPanel.clearListeners();
            this.methodsPanel.destroy();
        }
        if(this.eventsPanel){
            this.eventsPanel.clearListeners();
            this.eventsPanel.destroy();
        }
        Ext.Object.each(this.sourceConfig,function(index, item){
            if(item.editor && item.editor.destroy){
                if(item.getStore){
                    item.getStore().destroy();
                }
                item.editor.destroy();
            }
        });
        this.removeAll(true, true);
        this.callParent(arguments);
    }
});