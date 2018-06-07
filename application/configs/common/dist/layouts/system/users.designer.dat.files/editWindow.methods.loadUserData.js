this.childObjects.editWindow_form.getForm().load({
    scope: this,
    url: '[%wroot%][%admp%][%-%]user[%-%]userload',
    params: {id: this.recordId},
    waitMsg: appLang.LOADING,
    success: function (form, action) {
        this.setTitle(appLang.EDIT_USER + ': ' + form.findField('name').getValue());
        form.findField('setpass').setReadOnly(false);
        form.findField('setpass').setValue(0);
    },
    failure: app.formFailure
});

this.denyBlankPassword(null, false);