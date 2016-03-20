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
      'remoteSort' => true,
      'sorters' => '[{"field":"","direction":"DESC","property":"date","id":"designer.store.sortersModel-2"}]',
      'isExtended' => false,
    ),
    'state' => 
    array (
    ),
    'fields' => 
    array (
      'object' => 
      array (
        'class' => 'Ext_Virtual',
        'extClass' => 'Data_Field',
        'state' => 
        array (
          'config' => 
          array (
            'name' => 'object',
            'type' => 'string',
          ),
        ),
      ),
      'record_id' => 
      array (
        'class' => 'Ext_Virtual',
        'extClass' => 'Data_Field',
        'state' => 
        array (
          'config' => 
          array (
            'name' => 'record_id',
            'type' => 'integer',
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
      'object_title' => 
      array (
        'class' => 'Ext_Virtual',
        'extClass' => 'Data_Field',
        'state' => 
        array (
          'config' => 
          array (
            'name' => 'object_title',
            'type' => 'string',
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
          'url' => '[%wroot%][%admp%][%-%]logs[%-%]list',
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