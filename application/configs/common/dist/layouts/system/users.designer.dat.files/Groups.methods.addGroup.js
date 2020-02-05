var me = this;
Ext.Msg.prompt(appLang.MESSAGE, appLang.MSG_ENTER_NEW_GROUP_NAME, function (btn, text) {
            if (btn != 'ok' || !text.length) {
                return
            }
            Ext.Ajax.request({
                url: '[%wroot%][%admp%][%-%]user[%-%]addgroup',
                method: 'post',
                params: {
                    'name': text
                },
                success: function (response, request) {
                    response = Ext.JSON.decode(response.responseText);
                    if (response.success) {
                        me.getStore().load();
                    } else {
                        Ext.Msg.alert(response.msg);
                    }
                },
                failure: app.ajaxFailure
            });

});