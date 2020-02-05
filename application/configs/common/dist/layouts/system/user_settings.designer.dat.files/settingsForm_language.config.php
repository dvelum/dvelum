<?php return array (
  'id' => 'settingsForm_language',
  'class' => 'Ext_Virtual',
  'extClass' => 'Form_Field_Combobox',
  'name' => 'settingsForm_language',
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
          'value' => 'languagesStore',
        ),
      ),
      'valueField' => 'id',
      'displayField' => 'id',
      'forceSelection' => true,
      'queryMode' => 'local',
      'name' => 'language',
      'isExtended' => false,
      'fieldLabel' => '[js:] appLang.LANGUAGE',
    ),
  ),
); 