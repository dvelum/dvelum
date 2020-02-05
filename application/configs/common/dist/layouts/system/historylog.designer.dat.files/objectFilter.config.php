<?php return array (
  'id' => 'objectFilter',
  'class' => 'Ext_Component_Filter',
  'extClass' => 'Component_Filter',
  'name' => 'objectFilter',
  'state' => 
  array (
    'config' => 
    array (
      'store' => 'dataStore',
      'storeField' => 'object',
      'local' => false,
      'autoFilter' => true,
      'isExtended' => false,
    ),
    'viewObject' => 
    array (
      'class' => 'Form_Field_Combobox',
      'state' => 
      array (
        'config' => 
        array (
          'store' => 'objectsStore',
          'valueField' => 'id',
          'displayField' => 'title',
          'forceSelection' => true,
          'emptyText' => '[js:] appLang.ALL',
          'triggers' => '{ 	reset:{ 		hideOnReadOnly:true, 		cls: \'x-form-clear-trigger\', 		tooltip:appLang.RESET, 		handler: function(field,trigger,e) { 			field.reset(); 			e.stopEvent(); 		}, 	} }',
          'minWidth' => 250.0,
        ),
      ),
    ),
  ),
); 