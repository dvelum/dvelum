<?php return array (
  'id' => 'langsStoreModel',
  'class' => 'Ext_Model',
  'extClass' => 'Model',
  'name' => 'langsStoreModel',
  'state' => 
  array (
    'config' => 
    array (
      'idProperty' => 'id',
      'isExtended' => true,
    ),
    'state' => 
    array (
      '_validations' => 
      array (
      ),
      '_associations' => 
      array (
      ),
    ),
    'fields' => 
    array (
      'id' => 
      array (
        'class' => 'Ext_Virtual',
        'extClass' => 'Data_Field',
        'state' => 
        array (
          'config' => 
          array (
            'name' => 'id',
            'type' => 'string',
          ),
        ),
      ),
      'title' => 
      array (
        'class' => 'Ext_Virtual',
        'extClass' => 'Data_Field',
        'state' => 
        array (
          'config' => 
          array (
            'name' => 'title',
            'type' => 'string',
          ),
        ),
      ),
    ),
  ),
); 