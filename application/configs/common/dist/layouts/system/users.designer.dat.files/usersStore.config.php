<?php return array (
  'id' => 'usersStore',
  'class' => 'Ext_Data_Store',
  'extClass' => 'Data_Store',
  'name' => 'usersStore',
  'state' => 
  array (
    'config' => 
    array (
      'autoLoad' => true,
      'model' => 'usersStoreModel',
      'remoteSort' => true,
      'isExtended' => true,
    ),
    'state' => 
    array (
    ),
    'fields' => 
    array (
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
          'url' => '[%wroot%][%admp%][%-%]user[%-%]userlist',
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
          'idProperty' => 'id',
        ),
      ),
    ),
    'writer' => '',
  ),
); 