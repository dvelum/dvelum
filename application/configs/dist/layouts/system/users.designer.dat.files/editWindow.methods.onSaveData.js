this.childObjects.editWindow_form.getForm().submit({
    clientValidation: true,
    waitMsg: appLang.SAVING,
    method: 'post',
    url: '[%wroot%][%admp%][%-%]user[%-%]usersave',
    scope: this,
    success: function (form, action) {
        this.fireEvent('dataSaved');
        this.close();
    },
    failure: app.formFailure
});   