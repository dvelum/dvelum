<?php return array (
  'id' => 'statusStore',
  'class' => 'Ext_Data_Store',
  'extClass' => 'Data_Store',
  'name' => 'statusStore',
  'state' => 
  array (
    'config' => 
    array (
      'data' => '[  {id:1 , title:appLang.ACTIVE}, {id:0 , title:appLang.DISABLED}]',
      'autoLoad' => false,
      'model' => 'statusStoreModel',
      'isExtended' => true,
      'defineOnly' => true,
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