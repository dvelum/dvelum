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
      'autoFilter' => true,
      'isExtended' => false,
    ),
    'viewObject' => 
    array (
      'class' => 'Component_Field_System_Objectlink',
      'state' => 
      array (
        'config' => 
        array (
          'objectName' => 'user',
          'controllerUrl' => '/[%admp%]/logs/',
        ),
      ),
    ),
  ),
); 