<?php return array (
  'id' => 'editWindow_login',
  'class' => 'Ext_Virtual',
  'extClass' => 'Form_Field_Text',
  'name' => 'editWindow_login',
  'state' => 
  array (
    'config' => 
    array (
      'allowBlank' => false,
      'enableKeyEvents' => true,
      'vtype' => 'alphanum',
      'name' => 'login',
      'validateOnBlur' => false,
      'listeners' => '{keyup: {  fn: this.checkLogin, scope: this,  buffer: 400 } }',
      'fieldLabel' => '[js:] appLang.LOGIN',
    ),
  ),
); 