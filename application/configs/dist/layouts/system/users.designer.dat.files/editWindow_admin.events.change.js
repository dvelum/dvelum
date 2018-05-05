var groupField = this.childObjects.editWindow_form.getForm().findField('group_id');
if (newValue ) {
    groupField.show();
    groupField.enable();
} else {
    groupField.hide();
    groupField.disable();
}