/**
 *
 * @event addItemCall
 * @param record
 *
 */
Ext.define('app.selectWindow', {
    extend: 'Ext.Window',
    dataPanel: null,
    constructor: function (config) {
        config.items = [];
        config = Ext.apply({
            width: app.checkWidth(config.width),
            height: app.checkHeight(config.height),
            layout: 'fit',
            modal: true
        }, config || {});
        this.callParent(arguments);
    },
    initComponent: function () {
        this.items = [this.dataPanel];

        this.buttons = [{
            text: appLang.SELECT,
            scope: this,
            handler: this.selectItem
        }, {
            text: appLang.CLOSE,
            scope: this,
            handler: this.close
        }];

        this.callParent(arguments);

        this.down('gridpanel').on('itemdblclick', function (view, record, number, event, options) {
            this.sendSelected(record);
        }, this);

        this.on('show', function () {
            app.checkSize(this);
        });
    },
    sendSelected: function (record) {
        this.fireEvent('itemSelected', record);
        var me = this;
        if (me.isVisible()) {
            me.mask('Item selected');
            setTimeout(function () {
                me.unmask();
            }, 300);
        }
    },
    selectItem: function () {
        var sm;
        if (Ext.isEmpty(this.dataPanel.dataGrid)) {
            sm = this.down('gridpanel').getSelectionModel();
        } else {
            sm = this.dataPanel.dataGrid.getSelectionModel();
        }

        if (!sm.hasSelection()) {
            Ext.MessageBox.alert(appLang.MESSAGE, appLang.MSG_SELECT_ITEM_FOR_ADDING);
            return;
        }
        this.sendSelected(sm.getSelection()[0]);
    }
});
