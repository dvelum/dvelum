<?php return array (
  'id' => 'User_AuthModel',
  'class' => 'Ext_Model',
  'extClass' => 'Model',
  'name' => 'User_AuthModel',
  'state' => 
  array (
    'config' => 
    array (
      'idProperty' => 'id',
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
      'user' => 
      array (
        'class' => 'Ext_Virtual',
        'extClass' => 'Data_Field',
        'state' => 
        array (
          'config' => 
          array (
            'name' => 'user',
            'type' => 'string',
          ),
        ),
      ),
      'type' => 
      array (
        'class' => 'Ext_Virtual',
        'extClass' => 'Data_Field',
        'state' => 
        array (
          'config' => 
          array (
            'name' => 'type',
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
      'login' => 
      array (
        'class' => 'Ext_Virtual',
        'extClass' => 'Data_Field',
        'state' => 
        array (
          'config' => 
          array (
            'name' => 'login',
            'type' => 'string',
          ),
        ),
      ),
    ),
  ),
); 