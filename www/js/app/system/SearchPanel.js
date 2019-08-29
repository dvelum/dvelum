/**
 * Search toolbar.
 * Allows you to search in the set of fields.
 * Uses content filtering.
 * @author Kirill A Egorov 2011
 *
 * @event reset
 *
 */
Ext.define('SearchPanel', {
    extend: 'Ext.toolbar.Toolbar',
    alias: 'widget.searchpanel',
    /**
     * @var {Ext.form.TextField}
     */
    searchField: null,
    /**
     * @var {Ext.Button}
     */
    resetButton: null,
    /**
     * @var {Ext.data.Store}
     */
    store: null,
    /**
     * @var {Array}
     */
    fieldNames: [],
    /**
     * @property string  - local / remote
     */
    local: false,
    /**
     * @property string - last search query
     */
    lastQuery: '',
    /**
     * @property string  request param
     */
    searchParam: 'search',
    /**
     * Default field label
     * @property string
     */
    fieldLabel: null,
    /**
     * Hide field label
     */
    hideLabel: false,
    /**
     * The minimum number of characters the user must type before search starts
     */
    minChars: 1,

    constructor: function (config) {

        config = Ext.apply({
            border: false,
            bodyBorder: false,
            width: 230,
            hideLabel: false,
            frame: false,
            style: {
                border: 0
            },
            fieldLabel:appLang.SEARCH + ':'
        }, config || {});

        this.callParent(arguments);
    },

    initComponent: function () {

        this.resetButton = Ext.create('Ext.Button', {
            iconCls: 'deleteIcon',
            flat: false,
            tooltip: appLang.RESET,
            listeners: {
                'click': {
                    fn: function () {
                        this.searchField.setValue('');
                        this.clearFilter();
                    },
                    scope: this
                }
            }
        });

        this.searchField = Ext.create('Ext.form.field.Text', {
            enableKeyEvents: true,
            flex: 2,
            listeners: {
                'keyup': {
                    fn: this.startFilter,
                    buffer: 700,
                    scope: this
                }
            }
        });

        this.items = [];

        if (!this.hideLabel) {
            this.items.push(this.fieldLabel);
        }

        this.items.push(this.searchField, this.resetButton);
        this.callParent(arguments);
    },
    reset : function() {
        this.clearFilter();
        this.searchField.reset();
    },
    /**
     * Clear filter
     */
    clearFilter: function () {
        this.lastQuery = '';
        if (!this.local) {
            this.store.proxy.setExtraParam(this.searchParam, '');
            this.store.load({
                scope: this,
                callback: function (records, operation, success) {
                    this.fireEvent('reset');
                }
            });
        } else {
            this.fireEvent('reset');
        }
    },
    /**
     * Start filtering data
     */
    startFilter: function () {
        var query = this.searchField.getValue();

        if (this.lastQuery === query) {
            return;
        }

        if(query.length < this.minChars){
            return;
        }

        if (this.local) {
            this.clearFilter();
            this.store.filter({fn: this.isSearched, scope: this});
        } else {
            this.store.getProxy().setExtraParam(this.searchParam, this.searchField.getValue());
            if (Ext.isEmpty(this.store.isBufferedStore)) {
                this.store.loadPage(1);
            }else{
                this.store.load();
            }
        }
        this.lastQuery = query;
    },
    /**
     * Record filter function
     */
    isSearched: function (record) {
        var flag = false;
        var recordHandle = record;
        var searchText = this.searchField.getValue();
        var pattern = new RegExp(searchText, "gi");

        Ext.each(this.fieldNames, function (item) {
            if (pattern.exec(recordHandle.get(item)) != null) {
                flag = true;
                return;
            }
        }, this);

        return flag;
    },
    setValue: function (text) {
        this.searchField.setValue(text);
        this.startFilter();
    },
    getValue: function () {
        return this.searchField.getValue();
    },
    destroy: function () {
        this.searchField.destroy();
        this.resetButton.destroy();
        this.callParent(arguments);
    }
});