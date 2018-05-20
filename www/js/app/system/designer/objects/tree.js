/**
 * Designer Project tree
 * Displays hierarchy of project components
 *
 * @event dataSaved
 * @param id
 * @param className
 * @param title
 *
 * @event dataSaved
 *
 * @event objectRemoved
 */
Ext.define('designer.objects.TreeModel', {
    extend: 'Ext.data.Model',
    fields: [
        {name: 'id', type: 'string'},
        {name: 'text', type: 'string'},
        {name: 'objClass', type: 'string'},
        {name: 'isInstance', type: 'boolean'}
    ],
    idProperty: 'id'
});

Ext.define('designer.objects.StatefullTree', {
    extend: 'Ext.tree.Panel',
    getNodesState: function () {
        var state = {};
        this.getStore().each(function (node) {
            if (node.isExpanded()) {
                state[node.get('id')] = true;
            }
        });
        return state;
    },
    applyNodesState: function (state) {
        this.getStore().each(function (node) {
            if (Ext.isEmpty(node)) {
                return;
            }

            if (!Ext.isEmpty(state[node.get('id')])) {
                node.expand();
            } else {
                node.collapse();
            }
        });
    }
});

Ext.define('designer.objects.Tree', {
    extend: 'Ext.Panel',
    controllerUrl: '',
    listType: 'visual',
    layout: 'fit',
    firstLoad: true,
    selectedNode: false,
    initComponent: function () {

        this.nodesState = {};

        this.dataStore = Ext.create('Ext.data.TreeStore', {
            model: 'designer.objects.TreeModel',
            proxy: {
                type: 'ajax',
                url: this.controllerUrl + 'visuallist',
                reader: {
                    type: 'json',
                    idProperty: 'id'
                },
            },
            defaultRootId: '_ROOT_',
            defaultRootText: '/',
            clearOnLoad: true,
            autoLoad: false,
            listeners: {
                load: {
                    fn: function (store) {
                        this.treePanel.applyNodesState(this.nodesState);
                    },
                    scope: this
                }
            }
        });

        this.treePanel = Ext.create('designer.objects.StatefullTree', {
            store: this.dataStore,
            rootVisible: false,
            useArrows: true,
            viewConfig: {
                plugins: {
                    ptype: 'treeviewdragdrop'
                },
                listeners: {
                    drop: {
                        fn: this.sortChanged,
                        scope: this
                    },
                    nodedragover: function (targetNode, position, dragData) {
                        var rec = dragData.records[0],
                            isFirst = targetNode.isFirst(),
                            isRoot = targetNode.isRoot();

                        if (isRoot)
                            return false;
                        return (!(targetNode.parentNode.isRoot() && position != 'append'));
                    },
                    scope: this
                }
            }
        });

        this.treePanel.on('select', function (tree, record, index, eOpts) {
            this.selectedNode = record;
        }, this);

        this.dataStore.on('load', function () {
            if (this.selectedNode) {
                this.treePanel.getSelectionModel().select(this.selectedNode);
                this.treePanel.getView().focusRow(this.selectedNode);
            }
        }, this);


        this.treePanel.addListener('itemclick', function (view, record, element, index, e, eOpts) {
            this.fireEvent('itemSelected', record.get('id'), record.get('objClass'), record.get('text'), record.get('isInstance'));
        }, this);

        this.collapseBtn = Ext.create('Ext.Button', {
            icon: app.wwwRoot + 'i/system/collapse-tree.png',
            tooltip: desLang.collapseAll,
            listeners: {
                click: {
                    fn: function () {
                        this.treePanel.collapseAll();
                        this.collapseBtn.disable();
                        this.expandBtn.enable();
                    },
                    scope: this
                }
            }
        });
        this.expandBtn = Ext.create('Ext.Button', {
            tooltip: desLang.expandAll,
            icon: app.wwwRoot + 'i/system/expand-tree.png',
            disabled: true,
            listeners: {
                click: {
                    fn: function () {
                        this.treePanel.expandAll();
                        this.collapseBtn.enable();
                        this.expandBtn.disable();
                    },
                    scope: this
                }
            }
        });

        this.tbar = [this.collapseBtn, this.expandBtn,
            '->', {
                tooltip: desLang.remove,
                iconCls: 'deleteIcon',
                handler: this.removeObject,
                scope: this
            }
        ];

        this.items = [this.treePanel];
        this.callParent(arguments);

        this.treePanel.on('scrollershow', function (scroller) {
            if (scroller && scroller.scrollEl) {
                scroller.clearManagedListeners();
                scroller.mon(scroller.scrollEl, 'scroll', scroller.onElScroll, scroller);
            }
        }, this);
    },
    /**
     * Hard code fix for Ext.Tree.Store loading
     * @todo wait for official fix
     */
    reload: function () {
        this.nodesState = this.treePanel.getNodesState();
        //this.dataStore.getRootNode().removeAll();
        this.dataStore.removeAll();
        this.dataStore.load();
    },
    getStore: function () {
        return this.dataStore;
    },
    sortChanged: function (node, data, overModel, dropPosition, options) {
        var parentId = 0;
        var parentNode = null;
        if (dropPosition == 'append') {
            parentId = overModel.get('id');
            parentNode = overModel;
        } else {
            parentId = overModel.parentNode.get('id');
            parentNode = overModel.parentNode;
        }
        var childsOrder = [];
        parentNode.eachChild(function (node) {
            childsOrder.push(node.getId());
        }, this);

        Ext.Ajax.request({
            url: this.controllerUrl + 'sort',
            method: 'post',
            params: {
                'id': data.records[0].get('id'),
                'newparent': parentId,
                'order[]': childsOrder
            },
            scope: this,
            success: function (response, request) {
                response = Ext.JSON.decode(response.responseText);
                if (response.success) {
                    this.fireEvent('dataChanged');
                    this.updateLayout();
                } else {
                    this.reload();
                    Ext.Msg.alert(appLang.MESSAGE, response.msg);
                }
            },
            failure: app.formFailure
        });
    },
    /**
     * Remove component from project
     */
    removeObject: function () {
        var sm = this.treePanel.getSelectionModel();
        if (!sm.hasSelection() || sm.getSelection()[0].get('id') == '0') {
            Ext.Msg.alert(appLang.MESSAGE, desLang.msg_selectForRemove);
            return;
        }
        var me = this;
        var selected = sm.getSelection()[0];

        if (selected.get('objClass') == 'Docked') {
            Ext.Msg.alert(appLang.MESSAGE, desLang.cantDeleteDocked);
            return;
        }

        if (selected.get('objClass') == 'Designer_Project_Container') {
            return;
        }

        Ext.Ajax.request({
            url: this.controllerUrl + 'remove',
            method: 'post',
            params: {
                'id': selected.get('id')
            },
            success: function (response, request) {
                response = Ext.JSON.decode(response.responseText);
                if (response.success) {
                    me.fireEvent('objectRemoved');
                    me.fireEvent('dataChanged');
                    me.getStore().remove(selected);
                    //me.reload();
                } else {
                    Ext.Msg.alert(appLang.MESSAGE, response.msg);
                }
            },
            failure: app.formFailure
        });
    }

});