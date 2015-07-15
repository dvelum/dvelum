var store = this.getStore();
var data = app.collectStoreData(store);

data = Ext.encode(data);
Ext.Ajax.request({
    url: '[%wroot%][%admp%][%-%]user[%-%]savepermissions',
    method: 'post',
    params: {
        'data': data,
        'user_id': store.proxy.extraParams['user_id'],
        'group_id': store.proxy.extraParams['group_id']
    },
    success: function (response, request) {
        response = Ext.JSON.decode(response.responseText);
        if (response.success) {
            store.commitChanges();
        } else {
            Ext.Msg.alert(' ', response.msg);
        }
    },
    failure: app.ajaxFailure
});