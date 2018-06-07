<?php return array (
  'id' => 'dataStoreModel',
  'class' => 'Ext_Model',
  'extClass' => 'Model',
  'name' => 'dataStoreModel',
  'state' => 
  array (
    'config' => 
    array (
      'idProperty' => 'id',
      'defineOnly' => true,
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
      'name' => 
      array (
        'class' => 'Ext_Virtual',
        'extClass' => 'Data_Field',
        'state' => 
        array (
          'config' => 
          array (
            'name' => 'name',
            'type' => 'string',
          ),
        ),
      ),
      'date' => 
      array (
        'class' => 'Ext_Virtual',
        'extClass' => 'Data_Field',
        'state' => 
        array (
          'config' => 
          array (
            'name' => 'date',
            'type' => 'date',
            'dateFormat' => 'Y-m-d H:i:s',
          ),
        ),
      ),
      'id' => 
      array (
        'class' => 'Ext_Virtual',
        'extClass' => 'Data_Field',
        'state' => 
        array (
          'config' => 
          array (
            'name' => 'id',
            'type' => 'integer',
          ),
        ),
      ),
      'message' => 
      array (
        'class' => 'Ext_Virtual',
        'extClass' => 'Data_Field',
        'state' => 
        array (
          'config' => 
          array (
            'name' => 'message',
            'type' => 'string',
          ),
        ),
      ),
    ),
  ),
); 