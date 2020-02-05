<?php return array (
  'id' => 'dataStore',
  'class' => 'Ext_Data_Store',
  'extClass' => 'Data_Store',
  'name' => 'dataStore',
  'state' => 
  array (
    'config' => 
    array (
      'autoLoad' => true,
      'isExtended' => false,
    ),
    'state' => 
    array (
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
      'version' => 
      array (
        'class' => 'Ext_Virtual',
        'extClass' => 'Data_Field',
        'state' => 
        array (
          'config' => 
          array (
            'name' => 'version',
            'type' => 'string',
          ),
        ),
      ),
      'author' => 
      array (
        'class' => 'Ext_Virtual',
        'extClass' => 'Data_Field',
        'state' => 
        array (
          'config' => 
          array (
            'name' => 'author',
            'type' => 'string',
          ),
        ),
      ),
      'enabled' => 
      array (
        'class' => 'Ext_Virtual',
        'extClass' => 'Data_Field',
        'state' => 
        array (
          'config' => 
          array (
            'name' => 'enabled',
            'type' => 'boolean',
          ),
        ),
      ),
      'installed' => 
      array (
        'class' => 'Ext_Virtual',
        'extClass' => 'Data_Field',
        'state' => 
        array (
          'config' => 
          array (
            'name' => 'installed',
            'type' => 'boolean',
          ),
        ),
      ),
    ),
    'proxy' => 
    array (
      'class' => 'Ext_Virtual',
      'extClass' => 'Data_Proxy_Ajax',
      'state' => 
      array (
        'config' => 
        array (
          'directionParam' => 'pager[dir]',
          'limitParam' => 'pager[limit]',
          'simpleSortMode' => true,
          'sortParam' => 'pager[sort]',
          'startParam' => 'pager[start]',
          'url' => '[%wroot%][%admp%][%-%]externals[%-%]list',
          'reader' => false,
          'type' => 'ajax',
        ),
      ),
    ),
    'reader' => 
    array (
      'class' => 'Ext_Virtual',
      'extClass' => 'Data_Reader_Json',
      'state' => 
      array (
        'config' => 
        array (
          'rootProperty' => 'data',
          'totalProperty' => 'count',
        ),
      ),
    ),
    'writer' => '',
  ),
); 