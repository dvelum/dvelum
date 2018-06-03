<?php return array (
  'id' => 'operationFilter',
  'class' => 'Ext_Component_Filter',
  'extClass' => 'Component_Filter',
  'name' => 'operationFilter',
  'state' => 
  array (
    'config' => 
    array (
      'store' => 'dataStore',
      'storeField' => 'type',
      'local' => false,
      'autoFilter' => true,
      'isExtended' => false,
    ),
    'viewObject' => 
    array (
      'class' => 'Component_Field_System_Dictionary',
      'state' => 
      array (
        'config' => 
        array (
          'dictionary' => 'log_operation',
          'showAll' => true,
          'showReset' => false,
          'valueField' => 'id',
          'displayField' => 'title',
          'forceSelection' => true,
          'queryMode' => 'local',
          'triggerAction' => 'all',
          'emptyText' => '[js:] appLang.ALL',
          'width' => 120.0,
        ),
      ),
    ),
  ),
); 