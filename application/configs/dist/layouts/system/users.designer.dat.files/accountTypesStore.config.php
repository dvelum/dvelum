<?php return array (
  'id' => 'accountTypesStore',
  'class' => 'Ext_Data_Store',
  'extClass' => 'Data_Store',
  'name' => 'accountTypesStore',
  'state' => 
  array (
    'config' => 
    array (
      'data' => '[{id:\'1\' , title:appLang.BACKEND_USERS},  {id:\'0\' , title:appLang.FRONTEND_USERS}]',
      'autoLoad' => false,
      'model' => 'accountTypesStoreModel',
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