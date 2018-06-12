this.childObjects.settingsForm.getForm().submit({
    clientValidation: true,
    waitMsg: appLang.SAVING,
    method: 'post',
    url: '[%wroot%][%admp%][%-%]settings[%-%]settingsSave',
    scope: this,
    success: function (form, action) {
        location.reload();
    },
    failure: app.formFailure
});   