var val = field.getValue();
Ext.Ajax.request({
    url: '[%wroot%][%admp%][%-%]user[%-%]checkemail',
    method: 'post',
    params: {
        'id': this.childObjects.editWindow_form.getForm().findField('id').getValue(),
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