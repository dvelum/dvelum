var form = this.childObjects.userForm.getForm();
form.submit({
    clientValidation: true,
    waitMsg: appLang.SAVING,
    method: 'post',
    url: '[%wroot%][%admp%][%-%]settings[%-%]userSave',
    scope: this,
    failure: app.formFailure
});   