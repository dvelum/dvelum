<?php return array (
  'id' => 'editWindow_admin',
  'class' => 'Ext_Virtual',
  'extClass' => 'Form_Field_Checkbox',
  'name' => 'editWindow_admin',
  'state' => 
  array (
    'config' => 
    array (
      'name' => 'admin',
      'listeners' => '{change: {fn: this.checkIsAdmin, scope: this}}',
      'fieldLabel' => '[js:] appLang.ADMIN_PANEL_ACCESS',
    ),
  ),
); 