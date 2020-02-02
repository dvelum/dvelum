<?php return array (
  'id' => 'editWindow_setpass',
  'class' => 'Ext_Virtual',
  'extClass' => 'Form_Field_Checkbox',
  'name' => 'editWindow_setpass',
  'state' => 
  array (
    'config' => 
    array (
      'checked' => true,
      'submitValue' => true,
      'name' => 'setpass',
      'readOnly' => true,
      'listeners' => '{ change: { fn: this.denyBlankPassword, scope: this, buffer: 350 } }',
      'fieldLabel' => '[js:] appLang.CHANGE_PASSWORD',
    ),
  ),
); 