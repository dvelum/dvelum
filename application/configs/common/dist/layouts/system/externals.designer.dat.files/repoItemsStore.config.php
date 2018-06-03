<?php return array (
  'id' => 'repoItemsStore',
  'class' => 'Ext_Data_Store',
  'extClass' => 'Data_Store',
  'name' => 'repoItemsStore',
  'state' => 
  array (
    'config' => 
    array (
      'autoLoad' => false,
      'isExtended' => false,
    ),
    'state' => 
    array (
    ),
    'fields' => 
    array (
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
      'code' => 
      array (
        'class' => 'Ext_Virtual',
        'extClass' => 'Data_Field',
        'state' => 
        array (
          'config' => 
          array (
            'name' => 'code',
            'type' => 'string',
          ),
        ),
      ),
      'downloads' => 
      array (
        'class' => 'Ext_Virtual',
        'extClass' => 'Data_Field',
        'state' => 
        array (
          'config' => 
          array (
            'name' => 'downloads',
            'type' => 'string',
          ),
        ),
      ),
      'page' => 
      array (
        'class' => 'Ext_Virtual',
        'extClass' => 'Data_Field',
        'state' => 
        array (
          'config' => 
          array (
            'name' => 'page',
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
      'size' => 
      array (
        'class' => 'Ext_Virtual',
        'extClass' => 'Data_Field',
        'state' => 
        array (
          'config' => 
          array (
            'name' => 'size',
            'type' => 'string',
          ),
        ),
      ),
      'number' => 
      array (
        'class' => 'Ext_Virtual',
        'extClass' => 'Data_Field',
        'state' => 
        array (
          'config' => 
          array (
            'name' => 'number',
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
          'idParam' => 'code',
          'limitParam' => 'pager[limit]',
          'simpleSortMode' => true,
          'sortParam' => 'pager[sort]',
          'startParam' => 'pager[start]',
          'url' => '[%wroot%][%admp%][%-%]externals[%-%]repoitemslist',
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