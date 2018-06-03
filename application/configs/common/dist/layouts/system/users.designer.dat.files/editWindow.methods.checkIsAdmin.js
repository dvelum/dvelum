var groupField = this.childObjects.editWindow_form.getForm().findField('group_id');

if (checked) {
    groupField.show();
    groupField.enable();
} else {
    groupField.hide();
    groupField.disable();
    groupField.setValue('');
}