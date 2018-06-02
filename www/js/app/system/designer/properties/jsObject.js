/**
 * Properties panel for Grid object
 */
Ext.define('designer.properties.JSObject',{
    extend:'designer.properties.Panel',

    initComponent:function(){
        var me = this;
        this.sourceConfig = Ext.apply({
            'data': {
                editor: Ext.create('Ext.form.field.Text', {
                    listeners: {
                        focus: {
                            fn: function () {
                                me.showTypedDefaultsWindow('data');
                            },
                            scope: me
                        }
                    }
                }),
                renderer:function(){
                    return '...';
                }
            }
        });
        this.callParent();
    },
    showTypedDefaultsWindow: function (property) {
        var me = this;
        var source = this.dataGrid.getSource();
        var data = [];

        if (!Ext.isEmpty(source[property])) {
            data = Ext.JSON.decode(source[property]);
        }

        var win = Ext.create('designer.typedDefaultsWindow', {
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
});