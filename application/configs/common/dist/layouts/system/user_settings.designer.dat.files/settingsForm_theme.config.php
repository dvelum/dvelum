<?php return array (
  'id' => 'settingsForm_theme',
  'class' => 'Ext_Virtual',
  'extClass' => 'Form_Field_Combobox',
  'name' => 'settingsForm_theme',
  'state' => 
  array (
    'config' => 
    array (
      'store' => 
      array (
        'class' => 'Ext_Helper_Store',
        'state' => 
        array (
          'type' => 'store',
          'value' => 'themesStore',
        ),
      ),
      'valueField' => 'id',
      'displayField' => 'id',
      'forceSelection' => true,
      'queryMode' => 'local',
      'name' => 'theme',
      'isExtended' => false,
      'fieldLabel' => '[js:] appLang.THEME',
    ),
  ),
); 