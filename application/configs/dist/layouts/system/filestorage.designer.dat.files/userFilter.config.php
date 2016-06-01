<?php return array (
  'id' => 'userFilter',
  'class' => 'Ext_Component_Filter',
  'extClass' => 'Component_Filter',
  'name' => 'userFilter',
  'state' => 
  array (
    'config' => 
    array (
      'store' => 'dataStore',
      'storeField' => 'user_id',
      'local' => false,
      'autoFilter' => true,
    ),
    'viewObject' => 
    array (
      'class' => 'Component_Field_System_Objectlink',
      'state' => 
      array (
        'config' => 
        array (
          'objectName' => 'user',
          'controllerUrl' => '[%wroot%][%admp%][%-%]filestorage[%-%]',
          'minWidth' => 250.0,
        ),
      ),
    ),
  ),
); 