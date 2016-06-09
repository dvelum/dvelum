<?php return array (
  'id' => 'search',
  'class' => 'Ext_Virtual',
  'extClass' => 'Form_Field_Combobox',
  'name' => 'search',
  'state' => 
  array (
    'config' => 
    array (
      'store' => '[new:] searchStore',
      'valueField' => 'id',
      'displayField' => 'name',
      'listConfig' => '{     loadingText: appLang.SEARCHING,     emptyText: appLang.NO_RECORDS_TO_DISPLAY,     getInnerTpl: function() { 	return \'<b>{name}</b><br><span>{title}</span>\';     } }',
      'queryMode' => 'remote',
      'queryParam' => 'search',
      'typeAhead' => false,
      'hideTrigger' => true,
      'name' => 'search',
      'width' => 350.0,
      'fieldLabel' => '[js:] appLang.SEARCH',
      'labelAlign' => 'right',
      'labelWidth' => 100.0,
    ),
  ),
); 