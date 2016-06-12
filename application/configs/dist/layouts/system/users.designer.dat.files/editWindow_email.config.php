<?php return array (
  'id' => 'editWindow_email',
  'class' => 'Ext_Virtual',
  'extClass' => 'Form_Field_Text',
  'name' => 'editWindow_email',
  'state' => 
  array (
    'config' => 
    array (
      'allowBlank' => true,
      'enableKeyEvents' => true,
      'vtype' => 'email',
      'name' => 'email',
      'listeners' => '{  keyup: { fn: this.checkMail, scope: this,   buffer: 400 }  }',
      'fieldLabel' => '[js:] appLang.EMAIL',
    ),
  ),
); 