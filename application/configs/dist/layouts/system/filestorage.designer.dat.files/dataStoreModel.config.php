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
      'ext' => 
      array (
        'class' => 'Ext_Virtual',
        'extClass' => 'Data_Field',
        'state' => 
        array (
          'config' => 
          array (
            'name' => 'ext',
            'type' => 'string',
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
      'size' => 
      array (
        'class' => 'Ext_Virtual',
        'extClass' => 'Data_Field',
        'state' => 
        array (
          'config' => 
          array (
            'name' => 'size',
            'type' => 'float',
          ),
        ),
      ),
      'user_id' => 
      array (
        'class' => 'Ext_Virtual',
        'extClass' => 'Data_Field',
        'state' => 
        array (
          'config' => 
          array (
            'name' => 'user_id',
            'type' => 'integer',
          ),
        ),
      ),
      'user_name' => 
      array (
        'class' => 'Ext_Virtual',
        'extClass' => 'Data_Field',
        'state' => 
        array (
          'config' => 
          array (
            'name' => 'user_name',
            'type' => 'string',
          ),
        ),
      ),
    ),
  ),
); 