<?php return array (
  'id' => 'editWindow_group_id',
  'class' => 'Ext_Virtual',
  'extClass' => 'Form_Field_Combobox',
  'name' => 'editWindow_group_id',
  'state' => 
  array (
    'config' => 
    array (
      'store' => 'groupsStoreInstance',
      'valueField' => 'id',
      'displayField' => 'title',
      'forceSelection' => true,
      'queryMode' => 'remote',
      'name' => 'group_id',
      'disabled' => true,
      'hidden' => true,
      'fieldLabel' => '[js:] appLang.GROUP',
    ),
  ),
); 