var store = this.getStore();

Ext.Msg.confirm(appLang.CONFIRM, appLang.MSG_CONFIRM_DELETE +' ' + user.get('name')+'?', function (btn) {
    if (btn != 'yes') {
        return
    }

    Ext.Ajax.request({
        url: '[%wroot%][%admp%][%-%]user[%-%]removeuser',
        method: 'post',
        waitMsg: appLang.SAVING,
        params: {
            'id': user.get('id')
        },
        success: function (response, request) {
            response = Ext.JSON.decode(response.responseText);
            if (response.success) {
               store.remove(user);
            } else {
                Ext.MessageBox.alert(appLang.MESSAGE, response.msg);
            }
        }
    });
});