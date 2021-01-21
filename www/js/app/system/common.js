var Ext = Ext || {};

Ext.Loader.setConfig({
    enabled: false,
    disableCaching: true
});

Ext.ns('app');
Ext.tip.QuickTipManager.init();
Ext.data.DataReader.messageProperty = "msg";
Ext.state.Manager.setProvider(new Ext.state.LocalStorageProvider());

app.msgCt = false;
app.createBox = function (t, s) {
    return '<div class="msg"><h3>' + t + '</h3><p>' + s + '</p></div>';
};
/**
 * Application notification message
 * @param title
 * @param text
 */
app.msg = function (title, text) {
    if (!app.msgCt) {
        app.msgCt = Ext.core.DomHelper.insertFirst(document.body, {id: 'msg-div'}, true);
    }
    var s = Ext.String.format.apply(String, Array.prototype.slice.call(arguments, 1));
    var m = Ext.core.DomHelper.append(app.msgCt, app.createBox(title, s), true);
    m.hide();
    m.slideIn('t').ghost("t", {delay: 3000, remove: true});
};

/**
 * Model for ComboBox data using the fields: id - int, title - string fields
 */
Ext.define('app.comboModel', {
    extend: 'Ext.data.Model',
    idProperty: 'id',
    fields: [
        {name: 'id', type: 'integer'},
        {name: 'title', type: 'string'}
    ]
});
/**
 * Model for ComboBox data using the fields: id - string, title - string
 */
Ext.define('app.comboStringModel', {
    extend: 'Ext.data.Model',
    idProperty: 'id',
    fields: [
        {name: 'id', type: 'string'},
        {name: 'title', type: 'string'}
    ]
});
/**
 * Model for ComboBox data using the fields: name - string, value - string
 */
Ext.define('app.comboValueModel', {
    idProperty: 'name',
    extend: 'Ext.data.Model',
    fields: [
        {name: 'name', type: 'string'},
        {name: 'value', type: 'string'}
    ]
});

Ext.data.proxy.Ajax.override({
    type: 'ajax',
    config: {
        binary: false,
        headers: undefined,
        paramsAsJson: false,
        withCredentials: false,
        useDefaultXhrHeader: true,
        username: null,
        password: null,
        actionMethods: {
            create: 'POST',
            read: 'POST',
            update: 'POST',
            destroy: 'POST'
        }
    }
});
/*
 * Column renderer based on ComboBox data
 */
app.comboBoxRenderer = function (combo) {
    return function (value) {
        var idx = combo.getStore().findExact(combo.valueField, value);
        var rec = combo.getStore().getAt(idx);
        if (rec) {
            return rec.get(combo.displayField);
        } else {
            return '';
        }
    };
};
/**
 * method verifying the component’s height value and returning the maximum
 * available size if the component size does not fit the window
 * (to be used for window only)
 * @param {integer} size
 * @return integer
 */
app.checkHeight = function (size) {
    var viewSize = Ext.getBody().getViewSize();

    if (size > viewSize.height) {
        return (viewSize.height * 0.9);
    } else {
        return size;
    }
};
/**
 * The same as app.checkHeight , but for verifying the width
 * @param {integer} size
 * @return integer
 */
app.checkWidth = function (size) {
    var viewSize = Ext.getBody().getViewSize();

    if (size > viewSize.width) {
        return (viewSize.width * 0.9);
    } else {
        return size;
    }
};
/**
 * Verifies size and coordinates of the Ext.Window and reduces
 * the size/ changes coordinates of the window if it goes beyond
 * the visible area
 * @param {Ext.Window} window
 */
app.checkSize = function (window) {
    var width = window.getWidth();
    var height = window.getHeight();

    var checkedWidth = app.checkWidth(width);
    var checkedHeight = app.checkHeight(height);

    if (checkedWidth < width || checkedHeight < height)
        window.setSize(checkedWidth, checkedHeight);

    var position = window.getPosition();
    var setPos = false;

    if (position[0] < 0) {
        setPos = true;
        position[0] = 10;
    }

    if (position[1] < 0) {
        setPos = true;
        position[1] = 10;
    }

    if (setPos) {
        window.setPosition(position);
    }
};

app.comboTpl = Ext.create('Ext.XTemplate', '<tpl for="."><div class=\"app-combo-item\">', '{title}', '</div></tpl>');
app.comboListConfig = {
    getInnerTpl: function () {
        return '<div class="app-combo-item">{title}</div>';
    }
};

app.getCookie = function (name) {
    var prefix = name + "=";
    var cookieStartIndex = document.cookie.indexOf(prefix);
    if (cookieStartIndex == -1) return null;
    var cookieEndIndex = document.cookie.indexOf(";", cookieStartIndex + prefix.length);
    if (cookieEndIndex == -1) cookieEndIndex = document.cookie.length;
    return unescape(document.cookie.substring(cookieStartIndex + prefix.length, cookieEndIndex));
};
/*
 * Renderer for boolean data
 */
app.checkboxRenderer = function (value, metaData, record, rowIndex, colIndex, store) {
    if (value) {
        return '<img src="' + app.wwwRoot + 'i/system/yes.gif" data-qtip="' + appLang.YES + '">';
    } else {
        return '<img src="' + app.wwwRoot + 'i/system/no.png" data-qtip="' + appLang.NO + '">';
    }
};
/*
 * Renderer for boolean data
 */
app.publishRenderer = function (value, metaData, record, rowIndex, colIndex, store) {
    if (value) {
        metaData.tdCls = 'publishedRender';
        return '<img src="' + app.wwwRoot + 'i/system/yes.gif" data-qtip="' + appLang.PUBLISHED + '" />';
    } else {
        metaData.tdCls = 'notPublishedRender';
        return '<img src="' + app.wwwRoot + 'i/system/no.png" data-qtip="' + appLang.NOT_PUBLISHED + '" />';
    }
};
/*
 * Failure event handler when sending a form (to be defined manually)
 */
app.formFailure = function (form, action) {
    var task = new Ext.util.DelayedTask(function () {
        switch (action.failureType) {
            case Ext.form.Action.CLIENT_INVALID:
                Ext.Msg.alert(appLang.MESSAGE, appLang.FILL_FORM, false);
                break;
            case Ext.form.Action.CONNECT_FAILURE:
                Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION, false);
                break;
            case Ext.form.Action.SERVER_INVALID:
                Ext.Msg.alert(appLang.MESSAGE, action.result.msg, false);
                break;
            default :
                Ext.Msg.alert(appLang.MESSAGE, action.result.msg, false);

        }
    });
    task.delay(200);

};
/*
 * Failure event handler when sending an Ajax-request (to be defined manually)
 */
app.ajaxFailure = function (response, opts) {
    var task = new Ext.util.DelayedTask(function () {
        Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
    });
    task.delay(200);
};
/*
 * Specialized renderer for the ‘Document version’ column showing the information on the versioned objects
 */
app.versionRenderer = function (value, metaData, record, rowIndex, colIndex, store, view) {
    var cur = record.get('published_version');
    var last = record.get('last_version');

    if (Ext.isEmpty(cur)) {
        cur = '';
    }

    if (Ext.isEmpty(last)) {
        last = '';
    }

    if (cur >= last && cur != 0) {
        metaData.tdCls = 'renderLast';
    } else {
        metaData.tdCls = 'renderNotLast';
    }

    return cur + ' / ' + last;
};
/*
 * Specialized renderer for the ‘Object author’ column
 */
app.creatorRenderer = function (value, metaData, record, rowIndex, colIndex, store) {
    var user = record.get('user');
    var s = '';

    if (!Ext.isEmpty(user)) {
        s += user;
    }

    if (!Ext.isEmpty(record.get('date_created'))) {
        s += ' on ' + Ext.Date.format(record.get('date_created'), 'M d, Y H:i:s');
    }
    metaData.attr = 'data-qtip="' + s + '"';
    return s;
};
/*
 * Specialized renderer for the ‘Last update author’ column
 */
app.updaterRenderer = function (value, metaData, record, rowIndex, colIndex, store) {
    var updater = record.get('updater');
    var s = '';

    if (!Ext.isEmpty(updater)) {
        s += updater;
    }

    if (!Ext.isEmpty(record.get('date_updated'))) {
        s += ' on ' + Ext.Date.format(record.get('date_updated'), 'M d, Y H:i:s');
    }
    metaData.attr = 'data-qtip="' + s + '"';
    return s;
};
/**
 * Grid column with delete record button
 */
app.deleteColumn = function () {
    return {
        xtype: 'actioncolumn',
        width: 20,
        tooltip: appLang.SORT,
        dataIndex: 'id',
        align: 'center',
        items: [
            {
                iconCls: 'deleteIcon',
                tooltip: appLang.DELETE,
                handler: function (grid, rowIndex, colIndex) {
                    grid.getStore().removeAt(rowIndex);
                }
            }
        ]
    };
};
/*
 * Grid column, which allows sorting and removing elements
 */
app.sortColumn = function () {
    return {
        xtype: 'actioncolumn',
        width: 60,
        tooltip: appLang.SORT,
        dataIndex: 'id',
        items: [
            {
                iconCls: 'downIcon',
                handler: function (grid, rowIndex, colIndex) {
                    var total = grid.getStore().getCount();
                    if (rowIndex == total - 1)
                        return;

                    var sRec = grid.getStore().getAt(rowIndex);
                    grid.getStore().removeAt(rowIndex);
                    grid.getStore().insert(rowIndex + 1, sRec);
                    //grid.getStore().commitChanges();

                }
            }, {
                iconCls: 'upIcon',
                handler: function (grid, rowIndex, colIndex) {
                    //var total = grid.getStore().getCount();
                    if (rowIndex == 0) {
                        return;
                    }
                    var sRec = grid.getStore().getAt(rowIndex);
                    grid.getStore().removeAt(rowIndex);
                    grid.getStore().insert(rowIndex - 1, sRec);
                    //grid.getStore().commitChanges();
                }
            }, {
                iconCls: 'deleteIcon',
                tooltip: appLang.DELETE,
                handler: function (grid, rowIndex, colIndex) {
                    grid.getStore().removeAt(rowIndex);
                    // grid.getStore().commitChanges();
                }
            }
        ]
    };
};
/*
 * Specialized column renderer allowing to show a value on several lines
 * (supports line breaks, does not hide the content outside column borders)
 */
app.linesRenderer = function (value, metaData, record, rowIndex, colIndex, store) {
    metaData.style = 'white-space:normal !important;';
    return value;
};
/*
 * Specialized column renderer allowing to show a progress Bar
 * (values 0 - 100)
 */
app.progressRenderer = function (value, metaData, record, rowIndex, colIndex, store, vie) {
    var tmpValue = parseInt(value) / 100;
    var tmpText = parseInt(value) + '%';

    var progressRenderer = (function (pValue, pText) {
        var b = new Ext.ProgressBar({
            style: {width: '100%'},
            maxWidth: '100%'
        });
        return function (pValue, pText) {
            b.updateProgress(pValue, pText, true);
            return Ext.DomHelper.markup(b.getRenderTree());
        };
    })(tmpValue, tmpText);
    return progressRenderer(tmpValue, tmpText);
};
/**
 * Exception handler when trying to upload data to store
 * @param {Ext.data.proxy.Proxy} proxy
 * @param {Object} response - The response from the AJAX request
 * @param {Ext.data.Operation} operation - The operation that triggered request
 * @param {Object} eOpts - The options object passed to Ext.util.Observable.addListener.
 */
app.storeException = function (proxy, response, operation, eOpts) {
    if (response.aborted) {
        return;
    }
    if (response.responseText === null) {
        Ext.Msg.alert(appLang.MESSAGE, appLang.INVALID_RESPONSE);
        return;
    }
    try {
        var resp = Ext.JSON.decode(response.responseText);
    } catch (e){
        Ext.Msg.alert(appLang.MESSAGE, appLang.INVALID_RESPONSE);
    }
    if (resp && resp.msg != null) {
        Ext.Msg.alert(appLang.MESSAGE, resp.msg);
    } else {
        Ext.Msg.alert(appLang.MESSAGE, appLang.INVALID_RESPONSE);
    }
};

/**
 * Method creating url-address considering backend routing
 * features and taking an array of address elements as an argument
 * @param {Array} paths
 * @returns string
 */
app.createUrl = function (paths) {
    if (Ext.isArray(paths)) {
        return paths.join(app.delimiter);
    } else {
        return '';
    }
};


//Add the additional 'advanced' VTypes
Ext.apply(Ext.form.field.VTypes, {
    password: function (val, field) {
        if (field.initialPassField) {
            var pwd = field.up('form').getForm().findField(field.initialPassField);
            return (val == pwd.getValue());
        }
        return true;
    },
    passwordText: appLang.PASSNOTMATCH,
    valuematch: function (val, field) {
        if (field.initialPassField) {
            var pwd = field.up('form').getForm().findField(field.initialPassField);
            return (val == pwd.getValue());
        }
        return true;
    },
    valuematchText: appLang.VALUENOTMATCH,
    pagecode: function (val, field) {
        var exp = /^[a-zA-Z0-9_-]+$/;
        return exp.test(val);
    },
    pagecodeText: appLang.URL_PAGECODE_MATCH,
    pagecodeMask: /[a-z0-9_-]/i
});

/**
 * Method getting  the Data Store data,
 * all or modified data is available for collecting,
 * which is defined by the second parameter
 * @param {Ext.data.Store} store - data store to collect from
 * @param {boolean} onlyChanged - true to collect only new and changed data. Default to false.
 * @returns {Array}
 */
app.collectStoreData = function (store, onlyChanged) {

    onlyChanged = onlyChanged || false;
    var data = [];

    if (onlyChanged) {
        var newRec = store.getNewRecords();
        var updRec = store.getUpdatedRecords();
        var allRec = newRec.concat(updRec);

        Ext.each(allRec, function (item, index) {
            data.push(item.data);
        }, this);
    } else {
        store.each(function (item, index) {
            data.push(item.data);
        }, this);
    }
    return data;
};

/**
 * commitChanges and rejectChanges for {Ext.data.Store}
 */
Ext.override(Ext.data.AbstractStore, {

    commitChanges: function () {
        Ext.each(this.getUpdatedRecords(), function (rec) {
            rec.commit();
        });

        Ext.each(this.getNewRecords(), function (rec) {
            rec.commit();
            rec.phantom = false;
        });

        this.removed = [];
    },

    rejectChanges: function () {
        var rLength = this.removed.length;
        for (var i = 0; i < rLength; i++) {
            this.insert(this.removed[i].lastIndex || 0, this.removed[i]);
        }

        this.remove(this.getNewRecords());

        this.each(function (rec) {
            rec.reject();
        });

        this.removed = [];
    }
});
/*
 * Recursive set up of the checked property for a tree node
 */
app.checkChildNodes = function (node, isChecked) {
    node.eachChild(function (child) {
        child.set('checked', isChecked);
        if (!child.isLeaf()) {
            app.checkChildNodes(child, isChecked);
        }
    });
};

/**
 * @event load
 * @params {app.PermissionsStorage} me
 * @params {Object} permissions
 */
Ext.define('app.PermissionsStorage', {
    extend: 'Ext.Base',
    mixins: ['Ext.mixin.Observable'],
    permissionsLoaded: false,
    permissions: null,

    constructor: function (config) {
        // The `listeners` property is processed to add listeners and the config
        // is applied to the object.
        this.mixins.observable.constructor.call(this, config);
    },
    canView: function (module) {
        var perms = this.modulePermissions(module);
        if (perms === false) {
            return false;
        }
        return perms['view'];
    },
    canEdit: function (module) {
        var perms = this.modulePermissions(module);
        if (perms === false) {
            return false;
        }
        return perms['edit'];
    },
    canDelete: function (module) {
        var perms = this.modulePermissions(module);
        if (perms === false) {
            return false;
        }
        return perms['delete'];
    },
    canPublish: function (module) {
        var perms = this.modulePermissions(module);
        if (perms === false) {
            return false;
        }
        return perms['publish'];
    },
    loadData: function () {
        var me = this;
        Ext.Ajax.request({
            url: app.createUrl([app.admin, 'permissions', 'list']),
            method: 'post',
            scope: me,
            success: function (response, request) {
                response = Ext.JSON.decode(response.responseText);
                if (response.success) {
                    me.setData(response.data);
                }
            }
        });
    },
    /**
     * Set user permissions
     * @param data
     */
    setData: function (data) {
        this.permissionsLoaded = true;
        this.permissions = data;
        this.fireEvent('load', this, this.permissions);
    },
    /**
     * Get permissions for current user
     * @param  string module
     * @returns {*} | false
     */
    modulePermissions: function (module) {
        if (!this.permissionsLoaded) {
            Ext.Msg.alert(appLang.MESSAGE, 'Load permissions');
        }
        else {
            if (this.permissions[module] != undefined) {
                return this.permissions[module];
            } else {
                return false;
            }
        }
    }
});

//======= Overrides =============

(function () {
    Ext.override(Ext.tree.TreePanel, {
        getChecked: function (prop) {
            var prop = prop || null;
            var checked = [];
            this.getView().getTreeStore().getRootNode().cascadeBy(function (node) {
                if (node.data.checked) {
                    if (prop && node.data[prop]) checked.push(node.data[prop]);
                    else checked.push(node);
                }
            });
            return checked;
        }
    });
})();

(function () {
    Ext.override(Ext.data.proxy.Server, {
        constructor: function (config) {
            this.callOverridden([config]);
            this.addListener("exception", app.storeException, this);
        }
    });
})();

app.csrfToken = false;
app.getCSRFToken = function () {
    if (app.csrfToken !== false) {
        return app.csrfToken;
    }
    var meta = Ext.select("meta[name='csrf-token']");
    if (Ext.isEmpty(meta.elements)) {
        app.csrfToken = null;
    } else {
        app.csrfToken = meta.elements[0].content;
    }
    return app.csrfToken;
};

app.applyCSRFToken = function (options) {
    var token = app.getCSRFToken();
    if (token !== null) {
        options.headers = Ext.apply({
            'X-CSRF-Token': token
        }, options.headers || {});
    }
};
/**
 * Check if value is string
 */
app.isString = function (value) {
    return (typeof(value) == 'string');
};

/**
 * Html5 Notifycation API
 * @param {Object} params - notify params
 * params:
 * title: string default '' - notify window title
 * msg: string default '' - notify body
 * icon: string default app.wwwRoot + 'i/notify.png' - notify window icon
 * autoClose: integer default 0 (disable autoClose) - timeout before auto close notify (seconds)
 * @returns {Object|Boolean} - notifycation object or false on error
 */
app.html5Notify = function (params) {
    var notify = {
        title: '',
        msg: '',
        icon: app.wwwRoot + 'i/notify.png',
        autoClose: 0
    };
    Ext.apply(notify, params || {});
    var permitted = false;
    if (!("Notification" in window)) {
        console.log("This browser does not support desktop notification");
    } else if (Notification.permission === "granted") {
        permitted = true;
    } else if (Notification.permission !== 'denied') {
        Notification.requestPermission(function (permission) {
            if (permission === "granted") {
                permitted = true;
            }
        });
    }

    if (permitted) {
        var notifycation = new Notification(notify.title, {
            body: notify.msg,
            icon: notify.icon,
            lang: 'ru'
        });
        if (notify.autoClose) {
            setTimeout(function () {
                notifycation.close()
            }, notify.autoClose * 1000);
        }
        return notifycation;
    }
    return false;
};

/*
 * Adds support for CSRF protection token to ExtJS' Ajax methods
 */
Ext.Ajax.on('beforerequest', function (connection, options) {
    app.applyCSRFToken(options);
});
/*
 * Adds support for CSRF protection token to ExtJS' Ext.form.Basic actions
 */
(function () {
    Ext.override(Ext.form.Basic, {
        doAction: function (action, options) {
            app.applyCSRFToken(options);
            //call the original hide function
            this.callParent(arguments);
        }
    });
})();
/*
 * Override default value of submitEmptyText ExtJS' Ext.form.action.Submit
 */
(function () {
    Ext.override(Ext.form.action.Submit, {
        submitEmptyText: false
    });
})();
/**
 * Override store filter param
 */
(function () {
    Ext.override(Ext.data.proxy.Ajax, {
        filterParam: 'storefilter'
    });
})();

/*
 * Adds support for CSRF protection token to ExtJS' Ext.form.Basic fileupload actions
 */
(function () {
    Ext.override(Ext.data.Connection, {
        upload: function (form, url, params, options) {
            var token = app.getCSRFToken();
            if (token !== null) {
                if (params && params.length) {
                    params += '&xscrftoken=' + token;
                } else {
                    params = '?xscrftoken=' + token;
                }
            }
            this.callParent(arguments);
        }
    });
})();

(function () {
    Ext.override(Ext.panel.Table, {
        applyScrollable: function (scrollable) {
            if (this.view) {
                this.view.setScrollable(scrollable);
            } else {
                this.viewConfig = this.viewConfig || {};
                this.viewConfig.scrollable = scrollable;
            }
            return null;
        }
    });
})();

Ext.define('Ext.Fix.EXTJS-22672', {
    override: 'Ext.view.Table',

    getDefaultFocusPosition: function (fromComponent) {
        var me = this,
            store = me.dataSource,
            focusPosition = me.lastFocused,
            newPosition = new Ext.grid.CellContext(me).setPosition(0, 0),
            targetCell, scroller;
        if (fromComponent) {
            // Tabbing in from one of our column headers; the user will expect to land in that column.
            // Unless it is configured cellFocusable: false
            if (fromComponent.isColumn && fromComponent.cellFocusable !== false) {
                if (!focusPosition) {
                    focusPosition = newPosition;
                }
                focusPosition.setColumn(fromComponent);
                focusPosition.setView(fromComponent.getView());
            }
            // Tabbing in from the neighbouring TableView (eg, locking).
            // Go to column zero, same record
            else if (fromComponent.isTableView && fromComponent.lastFocused && fromComponent.ownerGrid === me.ownerGrid) {
                focusPosition = new Ext.grid.CellContext(me).setPosition(fromComponent.lastFocused.record, 0);
            }
        }
        // We found a position from the "fromComponent, or there was a previously focused context
        if (focusPosition) {
            scroller = me.getScrollable();
            // Record is not in the store, or not in the rendered block.
            // Fall back to using the same row index.
            if (!store.contains(focusPosition.record) || (scroller && !scroller.isInView(focusPosition.getRow()).y)) {
                focusPosition.setRow(store.getAt(Math.min(focusPosition.rowIdx, store.getCount() - 1)));
            }
        } else // All else failes, find the first focusable cell.
        {
            focusPosition = newPosition;
            // Find the first focusable cell.
            targetCell = me.el.down(me.getCellSelector() + '[tabIndex="-1"]');
            if (targetCell) {
                focusPosition.setPosition(me.getRecord(targetCell), me.getHeaderByCell(targetCell));
            } else // All visible columns are cellFocusable: false
            {
                focusPosition = null;
            }
        }
        return focusPosition;
    }
});

Ext.USE_NATIVE_JSON=false;