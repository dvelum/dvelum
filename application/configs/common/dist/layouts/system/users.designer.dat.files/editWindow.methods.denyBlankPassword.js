var handle = this.childObjects.editWindow_form.getForm();

if (!bool) {
    handle.findField('pass').disable();
    handle.findField('pass2').disable();
} else {
    handle.findField('pass').enable();
    handle.findField('pass2').enable();
}

handle.findField('pass').allowBlank = !bool;
handle.findField('pass2').allowBlank = !bool;