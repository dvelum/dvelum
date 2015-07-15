var val = field.getValue();
var e = field.up('form').getForm().findField('id').getValue();

Ext.Ajax.request({
    url: '[%wroot%][%admp%][%-%]user[%-%]checklogin',
    method: 'post',
    params: {
        'id': e,
        'value': val
    },
    success: function (response, request) {
        response = Ext.JSON.decode(response.responseText);
        if (response.success) {
            field.unsetActiveError();
            field.clearInvalid();
        } else {
            field.markInvalid(response.msg);
            field.setActiveError(response.msg);
        }
    },
    failure: app.ajaxFailure
});