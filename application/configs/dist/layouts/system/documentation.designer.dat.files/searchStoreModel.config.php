<?php return array (
  'id' => 'searchStoreModel',
  'class' => 'Ext_Model',
  'extClass' => 'Model',
  'name' => 'searchStoreModel',
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
      'hid' => 
      array (
        'class' => 'Ext_Virtual',
        'extClass' => 'Data_Field',
        'state' => 
        array (
          'config' => 
          array (
            'name' => 'hid',
            'type' => 'string',
          ),
        ),
      ),
      'methodId' => 
      array (
        'class' => 'Ext_Virtual',
        'extClass' => 'Data_Field',
        'state' => 
        array (
          'config' => 
          array (
            'name' => 'methodId',
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
      'path' => 
      array (
        'class' => 'Ext_Virtual',
        'extClass' => 'Data_Field',
        'state' => 
        array (
          'config' => 
          array (
            'name' => 'path',
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
      'fname' => 
      array (
        'class' => 'Ext_Virtual',
        'extClass' => 'Data_Field',
        'state' => 
        array (
          'config' => 
          array (
            'name' => 'fname',
            'type' => 'string',
          ),
        ),
      ),
    ),
  ),
); 