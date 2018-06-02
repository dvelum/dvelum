Ext.ns('designer');

designer.controllerUrl = '';
/*
 * Message box fom ExtJS Examples
 */
designer.msgCt = false;
designer.createBox = function (t, s) {
    return '<div class="msg"><h3>' + t + '</h3><p>' + s + '</p></div>';
};

designer.msg = function (title, text) {

    if (!designer.msgCt) {
        designer.msgCt = Ext.core.DomHelper.insertFirst(document.body, {id: 'msg-div'}, true);
    }
    var s = Ext.String.format.apply(String, Array.prototype.slice.call(arguments, 1));
    var m = Ext.core.DomHelper.append(designer.msgCt, designer.createBox(title, s), true);
    m.hide();
    m.slideIn('t').ghost("t", {delay: 3000, remove: true});
};

Ext.define('designer.application', {
    extend: 'Ext.Panel',

    rightPanel: null,
    centerPanel: null,

    /**
     * @var {designer.objects.Manager}
     */
    projectItems: null,
    rightBottom: null,
    componentsBar: null,
    topToolbar: null,

    codeEditor: null,
    eventsEditor: null,
    methodsEditor: null,
    viewSwitch: null,
    codeSwitch: null,

    activePropertyPanel: null,

    externalCommandReceiver: null,

    needRefresh: false,
    /**
     * Current project file path label
     * @propert {Ext.toolbar.TextItem}
     */
    projectPathLabel: null,

    autoRefreshSwitch: null,
    frameFirstLoad: true,

    activeFrameEl: null,
    frame1: null,
    //frame2:null,

    initComponent: function () {
        this.initCommandReceiver();
        this.initLayout();
        this.initToolbars();

        this.callParent();

        this.on('afterrender', function () {
            this.initFrame();
            this.checkIsLoaded();
        }, this);
    },

    initCommandReceiver: function () {
        var me = this;
        if (window.addEventListener) {
            window.addEventListener("message", function (event) {
                me.onCommand(event)
            });
        } else {
            // IE8
            window.attachEvent("onmessage", function (event) {
                me.onCommand(event)
            });
        }
    },

    onCommand: function (event) {

        var message = event.data;

        if (event.origin != window.location.origin) {
            return;
        }

        if (message.command && message.params) {
            this.runCommand(message.command, message.params);
        }
    },
    /**
     * Run external command
     * @param {string} command
     * @param {mixed} params
     */
    runCommand: function (command, params) {
        switch (command) {
            case 'windowSizeChanged':
                designer.msg(desLang.success, desLang.windowSizeChanged);
                break;
            case 'columnSizeChanged':
                designer.msg(desLang.success, desLang.columnSizeChanged);
                break;
            case 'columnMoved':
                designer.msg(desLang.success, desLang.columnMoved);
                break;
        }
    },

    initLayout: function () {
        this.layout = 'border';
        // Right
        this.projectItems = Ext.create('designer.objects.Manager', {
            region: 'north',
            split: true,
            height: 300,
            collapsible: true,
            controllerUrl: app.createUrl([designer.controllerUrl, 'objects', '']),
            listeners: {
                itemSelected: {
                    fn: this.showProperties,
                    scope: this
                },
                dataChanged: {
                    fn: this.refreshCodeframe,
                    scope: this
                },
                objectRemoved: {
                    fn: function () {
                        this.propertiesPanel.removeAll();
                        this.propertiesPanel.setTitle('');
                    },
                    scope: this
                }
            }
        });

        this.propertiesPanel = Ext.create('Ext.Panel', {
            region: 'center',
            split: true,
            layout: 'fit',
            title: desLang.properties,
            collapsible: false,
            border: false
        });

        this.rightPanel = Ext.create('Ext.Panel', {
            region: 'east',
            title: desLang.projectTree,
            split: true,
            minWidth: 250,
            width: 350,
            collapsible: true,
            layout: 'border',
            items: [this.projectItems, this.propertiesPanel]
        });


        // Center

        this.centerPanel = Ext.create('Ext.Panel', {
            region: 'center',
            //	title:desLang.layout,
            border: false,
            split: true,
            layout: 'fit'
            //html:'<iframe id="viewFrame" src="" />'
        });

        this.codeEditor = Ext.create('designer.codeEditor', {
            title: desLang.codeEditor,
            disabled: true,
            controllerUrl: app.createUrl([designer.controllerUrl, 'actionjs', ''])
        });

        this.eventsEditor = Ext.create('designer.eventsEditor', {
            title: desLang.eventsEditor,
            disabled: true,
            controllerUrl: app.createUrl([designer.controllerUrl, 'events', '']),
            listeners: {
                'eventsUpdated': {
                    fn: function () {
                        this.onChange();
                        if (!Ext.isEmpty(this.activePropertyPanel)) {
                            this.activePropertyPanel.refreshEvents();
                        }
                    },
                    scope: this
                }
            }
        });

        this.methodsEditor = Ext.create('designer.methodsEditor', {
            title: desLang.methodsEditor,
            disabled: true,
            controllerUrl: app.createUrl([designer.controllerUrl, 'methods', '']),
            listeners: {
                'methodsUpdated': {
                    fn: function () {
                        this.onChange();
                        if (!Ext.isEmpty(this.activePropertyPanel)) {
                            this.activePropertyPanel.refreshMethods();
                        }
                    },
                    scope: this
                }
            }
        });

        var bottomTabs = Ext.create('Ext.tab.Panel', {
            deferredRender: false,
            frame: false,
            //	title:desLang.code,
            layout: 'fit',
            items: [
                this.codeEditor,
                this.eventsEditor,
                this.methodsEditor
            ],
            listeners: {
                activate: {
                    fn: function (tab, opt) {
                        this.codeEditor.syncEditor();
                    },
                    scope: this
                }
            }
        });

        this.contentContainer = Ext.create('Ext.Container', {
            layout: 'card',
            activeItem: 0,
            deferredRender: false,
            header: false,
            region: 'center',
            items: [
                this.centerPanel,
                bottomTabs
            ]
        });

        this.items = [this.rightPanel, this.contentContainer];
    },

    initToolbars: function () {
        var pressed = app.cookieProvider.get('autoRefresh');
        if (pressed === undefined) {
            pressed = true;
        }

        this.autoRefreshSwitch = Ext.create('Ext.button.Button', {
            tooltip: desLang.autoRefresh,
            iconCls: 'autoRefreshIcon',
            showType: 'loaded',
            scope: this,
            enableToggle: true,
            pressed: pressed,
            listeners: {
                scope: this,
                toggle: function (btn, pressed) {
                    app.cookieProvider.set('autoRefresh', pressed);
                }
            }
        });

        this.viewSwitch = Ext.create('Ext.button.Button', {
            iconCls: 'viewInterfaceIcon',
            text: desLang.layout,
            showType: 'loaded',
            enableToggle: true,
            pressed: true,
            toggleGroup: 'viewTypeGroup',
            scope: this,
            toggleHandler: function (btn, status) {
                if (status) {
                    this.contentContainer.getLayout().setActiveItem(0);
                    if (this.needRefresh) {
                        this.refreshCodeframe(true);
                    }
                } else {
                    this.contentContainer.getLayout().setActiveItem(1);
                }
            }
        });

        this.codeSwitch = Ext.create('Ext.button.Button', {
            iconCls: 'viewCodeIcon',
            text: desLang.code,
            showType: 'loaded',
            scope: this,
            enableToggle: true,
            pressed: false,
            toggleGroup: 'viewTypeGroup'
        });

        this.projectPathLabel = Ext.create('Ext.toolbar.TextItem', {});
        this.dockedItems = [{
            xtype: 'toolbar',
            dock: 'left',
            defaults: {
                showType: 'loaded',
                iconCls: 'add16',
                textAlign: 'left',
                scope: this,
                handler: this.addObject
            },
            items: [{
                text: desLang.container,
                iconCls: 'containerIcon',
                tooltip: desLang.add + ' ' + desLang.container,
                oClass: 'container'
            }, {
                text: desLang.panel,
                iconCls: 'panelIcon',
                tooltip: desLang.add + ' ' + desLang.panel,
                oClass: 'panel'
            }, {
                text: desLang.tabPanel,
                tooltip: desLang.add + ' ' + desLang.tabPanel,
                iconCls: 'tabIcon',
                oClass: 'tabpanel'
            }, {
                text: desLang.grid,
                iconCls: 'gridIcon',
                tooltip: desLang.add + ' ' + desLang.grid,
                oClass: 'grid'
            }, {
                text: desLang.toolbar,
                iconCls: 'toolbarPanelIcon',
                tooltip: desLang.add + ' ' + desLang.toolbar,
                oClass: '',
                handler: false,
                menu: Ext.create('Ext.menu.Menu', {
                    style: {
                        overflow: 'visible'
                    },
                    defaults: {
                        scope: this,
                        handler: this.addObject
                    },
                    items: [
                        {
                            text: 'Panel',
                            iconCls: 'toolbarPanelIcon',
                            oClass: 'Toolbar',
                            showType: 'loaded'
                        },
                        {
                            text: 'Separator',
                            iconCls: 'toolbarSeparatorIcon',
                            oClass: 'Toolbar_Separator',
                            showType: 'loaded'
                        },
                        {
                            text: 'Spacer',
                            iconCls: 'toolbarSpacerIcon',
                            oClass: 'Toolbar_Spacer',
                            showType: 'loaded'
                        },
                        {
                            text: 'Fill',
                            iconCls: 'toolbarFillIcon',
                            tooltip: desLang.tbFillDescription,
                            oClass: 'Toolbar_Fill',
                            showType: 'loaded'
                        },
                        {
                            text: 'Text Item',
                            iconCls: 'toolbarTextitemIcon',
                            oClass: 'Toolbar_Textitem',
                            showType: 'loaded'
                        },
                        {
                            text: desLang.pagingToolbar,
                            iconCls: 'pagingIcon',
                            tooltip: desLang.add + ' ' + desLang.pagingToolbar,
                            oClass: 'Toolbar_Paging',
                            showType: 'loaded'
                        }
                    ]
                })
            }, {
                text: desLang.menu,
                iconCls: 'menuIcon',
                tooltip: desLang.add + ' ' + desLang.menu,
                oClass: '',
                handler: false,
                menu: Ext.create('Ext.menu.Menu', {
                    style: {
                        overflow: 'visible'
                    },
                    defaults: {
                        scope: this,
                        handler: this.addObject
                    },
                    items: [
                        /*{
                         text:'Menu',
                         iconCls:'menuIcon',
                         oClass:'Menu',
                         showType:'loaded'
                         },*/
                        {
                            text: 'Item',
                            iconCls: 'toolbarTextitemIcon',
                            oClass: 'Menu_Item',
                            showType: 'loaded'
                        },
                        {
                            text: 'Separator',
                            iconCls: 'menuSeparatorIcon',
                            oClass: 'Menu_Separator',
                            showType: 'loaded'
                        },
                        {
                            text: 'Check Item',
                            iconCls: 'checkboxIcon',
                            oClass: 'Menu_Checkitem',
                            showType: 'loaded'
                        },
                        {
                            text: 'Date Picker',
                            iconCls: 'dateIcon',
                            oClass: 'Menu_Datepicker',
                            showType: 'loaded'
                        },
                        {
                            text: 'Color Picker',
                            iconCls: 'colorPickerIcon',
                            oClass: 'Menu_Colorpicker',
                            showType: 'loaded'
                        }
                    ]
                })
            }, {
                text: desLang.form,
                iconCls: 'formIcon',
                oClass: '',
                handler: false,
                menu: Ext.create('Ext.menu.Menu', {
                    style: {
                        overflow: 'visible'
                    },
                    defaults: {
                        scope: this,
                        handler: this.addObject
                    },
                    items: [
                        {
                            text: desLang.formPanel,
                            iconCls: 'formIcon',
                            tooltip: desLang.add + ' ' + desLang.form,
                            oClass: 'form',
                            showType: 'loaded'
                        },
                        {
                            text: 'Text',
                            iconCls: 'textFieldIcon',
                            oClass: 'Form_Field_Text',
                            showType: 'loaded'
                        }, {
                            text: 'Number',
                            iconCls: 'textFieldIcon',
                            oClass: 'Form_Field_Number',
                            showType: 'loaded'
                        }, {
                            text: 'Hidden',
                            iconCls: 'hiddenFieldIcon',
                            showType: 'loaded',
                            oClass: 'Form_Field_Hidden'
                        }, {
                            text: 'Checkbox',
                            iconCls: 'checkboxIcon',
                            oClass: 'Form_Field_Checkbox',
                            showType: 'loaded'
                        }, {
                            text: 'Textarea',
                            iconCls: 'textareaIcon',
                            tooltip: desLang.tbFillDescription,
                            oClass: 'Form_Field_Textarea',
                            showType: 'loaded'
                        }, {
                            text: 'Htmleditor',
                            iconCls: 'htmlEditorIcon',
                            oClass: 'Form_Field_Htmleditor',
                            showType: 'loaded'
                        }, {
                            text: 'File',
                            iconCls: 'fileIcon',
                            oClass: 'Form_Field_File',
                            showType: 'loaded'
                        }, {
                            text: 'Radio',
                            iconCls: 'radioIcon',
                            oClass: 'Form_Field_Radio',
                            showType: 'loaded'
                        }, {
                            text: 'Time',
                            iconCls: 'clockIcon',
                            oClass: 'Form_Field_Time',
                            showType: 'loaded'
                        }, {
                            text: 'Date',
                            iconCls: 'dateIcon',
                            oClass: 'Form_Field_Date',
                            showType: 'loaded'
                        }, {
                            text: 'Fieldset',
                            iconCls: 'fieldsetIcon',
                            oClass: 'Form_Fieldset',
                            showType: 'loaded'
                        }, {
                            text: 'Display Field',
                            iconCls: 'displayfieldIcon',
                            oClass: 'Form_Field_Display',
                            showType: 'loaded'
                        }, {
                            text: 'Field Container',
                            iconCls: 'fieldContainerIcon',
                            oClass: 'Form_Fieldcontainer',
                            showType: 'loaded'
                        }, {
                            text: 'Checkbox group',
                            iconCls: 'checkboxGroupIcon',
                            oClass: 'Form_Checkboxgroup',
                            showType: 'loaded'
                        }, {
                            text: 'Radio group',
                            iconCls: 'radioGroupIcon',
                            oClass: 'Form_Radiogroup',
                            showType: 'loaded'
                        }, {
                            text: 'Combobox',
                            iconCls: 'comboboxFieldIcon',
                            showType: 'loaded',
                            oClass: 'Form_Field_Combobox'
                        }, {
                            text: 'Tag Field',
                            iconCls: 'tagIcon',
                            showType: 'loaded',
                            oClass: 'Form_Field_Tag'
                        }
                    ]
                })
            }, {
                text: desLang.buttons,
                iconCls: 'buttonIcon',
                tooltip: desLang.add + ' ' + desLang.buttons,
                oClass: '',
                handler: false,
                menu: Ext.create('Ext.menu.Menu', {
                    style: {
                        overflow: 'visible'
                    },
                    defaults: {
                        scope: this,
                        handler: this.addObject
                    },
                    items: [{
                        text: desLang.button,
                        iconCls: 'buttonIcon',
                        tooltip: desLang.add + ' ' + desLang.button,
                        oClass: 'Button',
                        showType: 'loaded'
                    }, {
                        text: desLang.splitButton,
                        iconCls: 'buttonSplitIcon',
                        tooltip: desLang.add + ' ' + desLang.splitButton,
                        oClass: 'Button_Split',
                        showType: 'loaded'
                    }, {
                        text: desLang.buttonGroup,
                        iconCls: 'buttonGroupIcon',
                        tooltip: desLang.add + ' ' + desLang.buttonGroup,
                        oClass: 'Buttongroup',
                        showType: 'loaded'
                    }]
                })
            }, {
                text: desLang.tree,
                iconCls: 'treeIcon',
                tooltip: desLang.add + ' ' + desLang.tree,
                oClass: 'tree'
            },
                {
                    text: 'Image',
                    iconCls: 'imageIcon',
                    tooltip: desLang.add + ' ' + desLang.image,
                    oClass: 'image'
                }, {
                    text: desLang.view,
                    iconCls: 'viewViewIcon',
                    tooltip: desLang.add + ' ' + desLang.view,
                    oClass: 'view'
                },
                '-',
                {
                    text: desLang.window,
                    iconCls: 'windowIcon',
                    tooltip: desLang.add + ' ' + desLang.window,
                    oClass: 'window'
                }, '-',
                {
                    text: desLang.store,
                    iconCls: 'storeIcon',
                    tooltip: desLang.add + ' ' + desLang.store,
                    oClass: 'Data_Store'
                },
                {
                    text: desLang.treeStore,
                    iconCls: 'storeIcon',
                    tooltip: desLang.add + ' ' + desLang.treeStore,
                    oClass: 'Data_Store_Tree'
                },
                {
                    text: desLang.bufferedStore,
                    iconCls: 'storeIcon',
                    tooltip: desLang.add + ' ' + desLang.bufferedStore,
                    oClass: 'Data_Store_Buffered'
                }, '-', {
                    text: desLang.model,
                    iconCls: 'modelIcon',
                    tooltip: desLang.add + ' ' + desLang.model,
                    oClass: 'Model'
                }, '-', {
                    text: desLang.components,
                    iconCls: 'panelIcon',
                    oClass: '',
                    showType: 'loaded',
                    handler: false,
                    menu: Ext.create('Ext.menu.Menu', {
                        style: {
                            overflow: 'visible'
                        },
                        defaults: {
                            scope: this,
                            handler: this.addObject
                        },
                        items: [
                            {
                                text: 'CRUD Window',
                                iconCls: 'objectWindowIcon',
                                oClass: 'Component_Window_System_Crud',
                                showType: 'loaded'
                            }, {
                                text: 'CRUD VC Window',
                                iconCls: 'objectWindowIcon',
                                oClass: 'Component_Window_System_Crud_Vc',
                                showType: 'loaded'
                            },
                            {
                                text: desLang.field,
                                showType: 'loaded',
                                iconCls: 'textFieldIcon',
                                menu: Ext.create('Ext.menu.Menu', {
                                    style: {
                                        overflow: 'visible'
                                    },
                                    defaults: {
                                        scope: this,
                                        handler: this.addObject
                                    },
                                    items: [
                                        {
                                            text: desLang.searchField,
                                            iconCls: 'textFieldIcon',
                                            oClass: 'Component_Field_System_Searchfield',
                                            showType: 'loaded'
                                        }, {
                                            text: desLang.dictionaryField,
                                            iconCls: 'comboboxFieldIcon',
                                            oClass: 'Component_Field_System_Dictionary',
                                            showType: 'loaded'
                                        }, {
                                            text: desLang.mediaHtmlField,
                                            iconCls: 'textMediaFieldIcon',
                                            oClass: 'Component_Field_System_Medialibhtml',
                                            showType: 'loaded'
                                        }, {
                                            text: desLang.mediaItemField,
                                            iconCls: 'resourceFieldIcon',
                                            oClass: 'Component_Field_System_Medialibitem',
                                            showType: 'loaded'
                                        }, {
                                            text: desLang.relatedItemsGrid,
                                            iconCls: 'gridIcon',
                                            oClass: 'Component_Field_System_Related',
                                            showType: 'loaded'
                                        }, {
                                            text: desLang.objectLinkField,
                                            iconCls: 'olinkIcon',
                                            oClass: 'Component_Field_System_Objectlink',
                                            showType: 'loaded'

                                        }, {
                                            text: desLang.objectsListPanel,
                                            iconCls: 'gridIcon',
                                            oClass: 'Component_Field_System_Objectslist',
                                            showType: 'loaded'
                                        }
                                    ]
                                })
                            },
                            {
                                text: desLang.storeFilter,
                                handler: this.addObject,
                                oClass: 'Component_Filter',
                                showType: 'loaded'
                            },{
                                text: desLang.jsObject,
                                handler:this.addObject,
                                oClass: 'Component_JSObject',
                                showType: 'loaded'
                            }
                        ]
                    })
                }, {
                    text: desLang.addInstance,
                    iconCls: 'containerIcon',
                    oClass: '',
                    showType: 'loaded',
                    tooltip: desLang.addInstanceTip,
                    handler: this.addInstance,
                    scope: this
                }, '-',
                {
                    text: desLang.templates,
                    iconCls: 'containerIcon',
                    oClass: '',
                    showType: 'loaded',
                    tooltip: desLang.componentTemplates,
                    scope: this,
                    handler: false,
                    menu: Ext.create('Ext.menu.Menu', {
                        style: {
                            overflow: 'visible'
                        },
                        defaults: {
                            scope: this,
                            handler: this.addTemplateObject
                        },
                        // global var from page
                        items: componentTemplates
                    })
                }
                /*,
                 {
                 icon:'/i/system/designer/chart.png',
                 tooltip: desLang.add + ' ' + desLang.chart
                 }*/
            ]
        }, {
            xtype: 'toolbar',
            dock: 'top',
            items: [
                {
                    text: desLang.layout,
                    showType: 'all',
                    menu: Ext.create('Ext.menu.Menu', {
                        style: {
                            overflow: 'visible'
                        },
                        items: [
                            {
                                text: desLang.newInterface,
                                iconCls: 'newIcon',
                                scope: this,
                                handler: this.createProject,
                                showType: 'empty'
                            }, {
                                text: desLang.load,
                                iconCls: 'openIcon',
                                scope: this,
                                handler: this.selectProject,
                                showType: 'empty'
                            }, {
                                text: desLang.save,
                                iconCls: 'saveIcon',
                                handler: this.saveProject,
                                scope: this,
                                showType: 'loaded'
                            }, {
                                text: desLang.close,
                                iconCls: 'exitIcon',
                                scope: this,
                                handler: this.closeProject,
                                showType: 'loaded'
                            }
                        ]
                    })
                }, {
                    tooltip: desLang.save,
                    iconCls: 'saveIcon',
                    handler: this.saveProject,
                    scope: this,
                    showType: 'loaded'
                }, '-', {
                    tooltip: desLang.refreshView,
                    iconCls: 'refreshIcon',
                    showType: 'loaded',
                    scope: this,
                    handler: function () {
                        this.onChange(true);
                    }
                },
                '-',
                this.autoRefreshSwitch,
                '-',
                desLang.viewMode + ' :',
                this.viewSwitch, ' / ',
                this.codeSwitch,
                {
                    iconCls: 'jsIcon',
                    showType: 'loaded',
                    tooltip: desLang.showProjectCode,
                    scope: this,
                    handler: this.showProjectCode
                },
                {
                    iconCls: 'debugIcon',
                    showType: 'loaded',
                    tooltip: desLang.showProjectContent,
                    href: app.root + 'debugger'
                },
                '-',
                {
                    iconCls: 'storeIcon',
                    text: desLang.dbConnections,
                    handler: function () {
                        Ext.create('app.orm.connections.Window', {
                            // global variable
                            dbConfigs: dbConfigsList,
                            controllerUrl: app.createUrl([app.admin, 'orm', 'connections', ''])
                        }).show();
                    },
                    showType: 'all'
                }, {
                    iconCls: 'configureIcon',
                    text: desLang.projectConfig,
                    showType: 'loaded',
                    handler: function () {
                        Ext.create('designer.configWindow', {
                            controllerUrl: designer.controllerUrl,
                            listeners: {
                                dataSaved: {
                                    fn: this.onChange,
                                    scope: this
                                }
                            }
                        }).show();
                    }
                }, '-',
                {
                    text: desLang.relatedProjectItems,
                    showType: 'loaded',
                    handler: this.showRelatedProjectItems,
                    scope: this
                },
                '-',
                {
                    text: desLang.backToAdminInterface,
                    showType: 'all',
                    href: app.createUrl([app.admin]),
                    hrefTarget: '_self'
                }, '-',

                '->',

                this.projectPathLabel

            ]
        }];
    },
    /**
     * Init view frame, create iframe
     */
    initFrame: function () {
        var contentEl = this.centerPanel.body;
        this.activeFrame = contentEl.appendChild({tag: 'iframe', cls: 'viewFrame', id: 'viewFrame1'});
        this.activeFrame.addListener('load', function () {
            contentEl.unmask();
        }, this);
    },
    /**
     * Clear View frame
     */
    clearFrame: function () {
        this.activeFrame.dom.src = '';
    },

    onChange: function (force) {
        this.loadInterface(force);
    },

    loadInterface: function (force) {
        this.projectItems.loadInfo();
        this.refreshCodeframe(force);
    },
    /**
     * Show window for creating project
     */
    createProject: function () {

        Ext.create('app.filesystemWindow', {
            title: desLang.createProject,
            controllerUrl: app.createUrl([designer.controllerUrl, 'fs', '']),
            viewMode: 'create',
            listeners: {
                fileCreated: {
                    fn: this.loadProject,
                    scope: this
                }
            }
        }).show();
    },
    /**
     * Show project selection window
     */
    selectProject: function () {

        Ext.create('app.filesystemWindow', {
            title: desLang.selectProject,
            viewMode: 'select',
            controllerUrl: app.createUrl([designer.controllerUrl, 'fs', '']),
            listeners: {
                fileSelected: {
                    fn: this.loadProject,
                    scope: this
                }
            }
        }).show();
    },
    /**
     * Load project
     * @param {string} name
     */
    loadProject: function (name) {
        var me = this;
        me.getEl().mask(appLang.LOADING);
        Ext.Ajax.request({
            url: app.createUrl([designer.controllerUrl, 'project', 'load']),
            method: 'post',
            params: {
                file: name
            },
            success: function (response, request) {
                response = Ext.JSON.decode(response.responseText);
                if (response.success) {
                    me.checkIsLoaded();
                } else {
                    Ext.Msg.alert(appLang.MESSAGE, response.msg);
                }
                me.getEl().unmask();
            },
            failure: function () {
                Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
                me.getEl().unmask();
            }
        });
    },
    /**
     * Check if project session is started. Load project config (used for restoring layout)
     */
    checkIsLoaded: function () {
        Ext.Ajax.request({
            url: app.createUrl([designer.controllerUrl, 'project', 'checkloaded']),
            method: 'post',
            scope: this,
            success: function (response, request) {
                response = Ext.JSON.decode(response.responseText);
                if (response.success) {
                    this.prepareInterface(true);
                    this.onChange();
                    this.projectPathLabel.setText(response.data.file);
                } else {
                    this.projectPathLabel.setText('');
                    this.prepareInterface(false);
                }
            },
            failure: function () {
                Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
            }
        });
    },
    /**
     * Save project
     * @param callback - optional
     */
    saveProject: function (callback) {
        this.codeEditor.saveCode();
        Ext.Ajax.request({
            url: app.createUrl([designer.controllerUrl, 'project', 'save']),
            method: 'post',
            success: function (response, request) {
                response = Ext.JSON.decode(response.responseText);
                if (response.success) {
                    designer.msg(appLang.MESSAGE, desLang.msg_projectSaved);
                    if (typeof callback == 'function') {
                        callback();
                    }
                } else {
                    Ext.Msg.alert(appLang.MESSAGE, response.msg);
                }
            },
            failure: function () {
                Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
            }
        });
    },
    /**
     * Close project
     */
    closeProject: function () {
        var me = this;
        Ext.Msg.confirm(appLang.CONFIRMATION, appLang.MSG_SAVE_BEFORE_CLOSE, function (btn) {
            if (btn == 'yes') {
                var callbackFunc = function () {
                    me.prepareInterface(false);
                    me.clearSession();
                };
                me.saveProject(callbackFunc);
            } else {
                me.clearSession();
                me.prepareInterface(false);
            }

        }, this);
    },
    /**
     * Prepare buttons
     */
    prepareInterface: function (asLoaded) {
        /*
         * Prepare menu items
         */
        Ext.each(this.getDockedItems('toolbar'), function (toolbar) {

            Ext.each(toolbar.query('button , menuitem'), function (item, index) {

                if (item.showType === 'all') {
                    return;
                }

                if (asLoaded) {
                    (item.showType == 'loaded') ? item.enable() : item.disable();
                } else {
                    (item.showType == 'loaded') ? item.disable() : item.enable();
                }
            }, this);
        }, this);

        /*
         * Init Code Editor
         */
        if (asLoaded) {
            this.rightPanel.enable();
            this.centerPanel.enable();
            this.codeEditor.enable();
            this.eventsEditor.enable();
            this.methodsEditor.enable();
            this.eventsEditor.getStore().load();
            this.methodsEditor.getStore().load();
            this.codeEditor.loadCode();
        } else {
            this.codeEditor.disable();
            this.codeEditor.setValue('');
            this.eventsEditor.disable();
            this.methodsEditor.disable();
            this.projectItems.clearData();
            this.propertiesPanel.removeAll();
            this.activePropertyPanel = null;
            this.projectPathLabel.setText('');
            this.rightPanel.disable();
            this.centerPanel.disable();
            this.clearFrame();
        }
    },
    /**
     * Clear session
     */
    clearSession: function () {
        Ext.Ajax.request({
            url: app.createUrl([designer.controllerUrl, 'project', 'close']),
            method: 'post',
            failure: function () {
                Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
            }
        });
    },
    /**
     * Add object to the project
     * @param btn
     */
    addObject: function (btn) {
        var oClass = btn.oClass;
        var me = this;
        var parent = 0;

        if (!oClass.length) {
            return;
        }

        Ext.MessageBox.prompt(appLang.MESSAGE, desLang.enterObjectName, function (btn, text) {
            if (btn != 'ok') {
                return;
            }

            var selection = me.projectItems.componentsTree.treePanel.getSelectionModel();

            if (selection.hasSelection()) {
                selected = selection.getSelection()[0];
                if (!selected.leaf) {
                    parent = selected.get('id');
                }
            }

            Ext.Ajax.request({
                url: app.createUrl([designer.controllerUrl, 'project', 'addobject']),
                method: 'post',
                params: {
                    'name': text,
                    'class': oClass,
                    'parent': parent
                },
                success: function (response, request) {
                    response = Ext.JSON.decode(response.responseText);
                    if (response.success) {
                        me.onChange();
                        designer.msg(desLang.success, desLang.objectAdded);
                    } else {
                        Ext.Msg.alert(appLang.MESSAGE, response.msg);
                    }
                },
                failure: function () {
                    Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
                }
            });
        });
    },
    /**
     * Add instance of extended object
     * @param {Ext.Button} btn
     */
    addInstance: function (btn) {
        var parent = '';
        var selection = this.projectItems.componentsTree.treePanel.getSelectionModel();

        if (selection.hasSelection()) {
            var selected = selection.getSelection()[0];
            if (!selected.leaf) {
                parent = selected.get('id');
            }
        }

        Ext.create('designer.addInstanceWindow', {
            controllerUrl: app.createUrl([designer.controllerUrl, 'project', '']),
            parentObject: parent,
            listeners: {
                objectAdded: {
                    fn: function (name) {
                        designer.msg(desLang.success, desLang.objectAdded);
                        this.onChange();
                    },
                    scope: this
                }
            }
        }).show();
    },
    /**
     * Add template
     * @param {Ext.Button} btn
     */
    addTemplateObject: function (btn) {
        var me = this;
        var parent = 0;
        Ext.MessageBox.prompt(appLang.MESSAGE, desLang.enterObjectName, function (resultBtn, text) {
            if (resultBtn != 'ok') {
                return;
            }

            var selection = me.projectItems.componentsTree.treePanel.getSelectionModel();

            if (selection.hasSelection()) {
                selected = selection.getSelection()[0];
                if (!selected.leaf) {
                    parent = selected.get('id');
                }
            }

            Ext.Ajax.request({
                url: app.createUrl([designer.controllerUrl, 'project', 'addtemplate']),
                method: 'post',
                params: {
                    'name': text,
                    'adapter': btn.adapter,
                    'parent': parent
                },
                success: function (response, request) {
                    response = Ext.JSON.decode(response.responseText);
                    if (response.success) {
                        me.onChange();
                        designer.msg(desLang.success, desLang.objectAdded);
                    } else {
                        Ext.Msg.alert(appLang.MESSAGE, response.msg);
                    }
                },
                failure: function () {
                    Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
                }
            });
        });
    },
    /**
     * Show object properties Panel
     * @param {string} objectName
     * @param {string} objectClass
     * @param {string} objectTitle
     * @param {bool} isInstance
     */
    showProperties: function (objectName, objectClass, objectTitle, isInstance) {

        var oldSearch = false;
        var oldEvent = false;
        var oldMethod = false;

        if (this.activePropertyPanel) {
            oldSearch = this.activePropertyPanel.getSearchText();
            oldEvent = this.activePropertyPanel.getEventsSearchText();
            oldMethod = this.activePropertyPanel.getMethodsSearchText();
        }
        this.propertiesPanel.removeAll(true, true);

        if (this.activePropertyPanel) {
            this.activePropertyPanel.clearListeners();
            this.activePropertyPanel.destroy();
        }

        var panelClass = null;

        if (objectClass === 'Designer_Project_Container') {
            return;
        }

        if (!isInstance) {
            switch (objectClass) {
                case 'Panel':
                    panelClass = 'designer.properties.Panel';
                    break;
                case 'Grid':
                    panelClass = 'designer.properties.Grid';
                    break;
                case 'Store':
                case 'Data_Store':
                    panelClass = 'designer.properties.Store';
                    break;
                case 'Data_Store_Tree':
                    panelClass = 'designer.properties.TreeStore';
                    break;
                case 'Data_Store_Buffered':
                    panelClass = 'designer.properties.Store';
                    break;
                case 'Model':
                    panelClass = 'designer.properties.Model';
                    break;
                case 'Component_Window_System_Crud':
                case 'Component_Window_System_Crud_Vc':
                    panelClass = 'designer.properties.CrudWindow';
                    break;
                case 'Window' :
                    panelClass = 'designer.properties.Window';
                    break;
                case 'Form' :
                    panelClass = 'designer.properties.Form';
                    break;
                case 'Component_Filter':
                    panelClass = 'designer.properties.FilterComponent';
                    break;
                case 'Component_Field_System_Searchfield' :
                    panelClass = 'designer.properties.Search';
                    break;
                case 'Component_Field_System_Medialibitem' :
                    panelClass = 'designer.properties.MediaItem';
                    break;
                case 'Component_JSObject':
                    panelClass = 'designer.properties.JSObject';
                    break;
                default    :
                    if (objectClass.indexOf('Field_', 0) !== -1 || objectClass === 'Form_Checkboxgroup') {
                        panelClass = 'designer.properties.Field';
                    } else {
                        panelClass = 'designer.properties.Panel';
                    }
                    break;
            }
        } else {
            panelClass = 'designer.properties.Panel';
        }

        var me = this;
        this.propertiesPanel.suspendEvents(true);

        this.propertiesPanel.setTitle('<span style="color:blue">' + objectTitle + ' </span> ' + desLang.properties);

        this.activePropertyPanel = Ext.create(panelClass, {
            controllerUrl: app.createUrl([designer.controllerUrl, 'properties', '']),
            eventsControllerUrl: app.createUrl([designer.controllerUrl, 'events', '']),
            methodsControllerUrl: app.createUrl([designer.controllerUrl, 'methods', '']),
            showMethods: true,
            objectName: objectName,
            application: me,
            listeners: {
                'dataSaved': {
                    fn: function (field) {
                        me.refreshCodeframe();
                        if (!Ext.isEmpty(field)) {
                            if (field === 'isExtended') {
                                this.methodsEditor.getStore().load();
                            }
                        }
                    },
                    scope: me
                },
                'objectsUpdated': {
                    fn: me.onChange,
                    scope: me
                },
                'eventsUpdated': {
                    fn: function () {
                        me.eventsEditor.getStore().load();
                        me.onChange();
                    },
                    scope: me
                }, 'methodsUpdated': {
                    fn: function () {
                        me.methodsEditor.getStore().load();
                        me.onChange();
                    },
                    scope: me
                }, 'afterLoad': {
                    fn: function () {
                        if (oldSearch) {
                            this.activePropertyPanel.setSearchText(oldSearch);
                            oldSearch = false;
                        }

                        if (oldEvent) {
                            this.activePropertyPanel.setEventsSearchText(oldEvent);
                            oldEvent = false;
                        }

                        if (oldMethod) {
                            this.activePropertyPanel.setMethodsSearchText(oldMethod);
                            oldMethod = false;
                        }
                    },
                    scope: me
                }

            }
        });


        this.propertiesPanel.add(this.activePropertyPanel);
        this.propertiesPanel.resumeEvents();

    },
    /**
     * Refresh view
     * @param {boolean} force
     */
    refreshCodeframe: function (force) {
        var forceLayout = force || false;
        var contentEl = this.centerPanel.body;

        /*
         * Ignore for code mode
         */
        if (!this.viewSwitch.pressed) {
            this.needRefresh = true;
            return;
        }

        if (forceLayout === true || this.autoRefreshSwitch.pressed || this.frameFirstLoad) {
            var dt = new Date();
            var newUrl = app.createUrl([designer.controllerUrl, 'viewframe', 'index', dt.getTime()]);
            contentEl.mask(desLang.loading);
            this.activeFrame.dom.src = newUrl;
            this.frameFirstLoad = false;
        }
    },
    /**
     * Get storage for visual objects
     * @returns {Ext.data.Store}
     */
    storesStore: function () {
        return this.projectItems.panelsTab.getStore();
    },
    /**
     * Get storage for "store" selector
     * @returns {Ext.data.Store}
     */
    getStoreSelector: function () {
        var store = this.projectItems.createStore('stores');
        store.proxy.setExtraParam('instances', true);
        return store;
    },
    /**
     Get storage for "store" selector
     * @returns {Ext.data.Store}
     */
    createStoresList: function (includeInstances) {
        var store = this.projectItems.createStore('store_selection');
        store.proxy.setExtraParam('stores', !includeInstances);
        store.proxy.setExtraParam('instances', includeInstances);
        return store;
    },
    /**
     * Get storage of project menus
     * @returns {Ext.data.Store}
     */
    getMenuStore: function () {
        return this.projectItems.createStore('menu');
    },
    /**
     * Get storage of project models
     * @returns {Ext.data.Store}
     */
    getModelsStore: function () {
        return this.projectItems.modelsStore;
    },
    /**
     * Send command for layout frame
     * @param {object} command -  {command:someString,'params':'mixed'}
     */
    sendCommand: function (command) {
        var view = this.activeFrame.dom.contentWindow;
        view.postMessage(command, window.location.origin);
    },
    /**
     * Show window with list of related project items
     */
    showRelatedProjectItems: function () {
        var dt = new Date();
        Ext.create('designer.relatedProjectItemsWindow', {
            listUrl: app.createUrl([designer.controllerUrl, 'objects', 'relatedprojectlist', dt.getTime()])
        }).show();
    },
    /**
     * Switch view type
     * @param {Number} index
     */
    switchView: function (index) {
        switch (index) {
            case 0 :
                this.viewSwitch.toggle(true);
                break;
            case 1 :
                this.codeSwitch.toggle(true);
                break;
        }
    },
    /**
     * Show project JS code
     */
    showProjectCode: function () {
        Ext.Ajax.request({
            url: app.createUrl([designer.controllerUrl, 'code', 'projectcode']),
            method: 'post',
            scope: this,
            success: function (response, request) {
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
                    title: desLang.sourceCode,
                    layout: 'fit',
                    width: 600,
                    height: 500,
                    modal: true,
                    maximizable: true,
                    items: [editor]
                }).show();
            },
            failure: function () {
                Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
            }
        });
    }
});
