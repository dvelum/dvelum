<?php return array (
  'id' => 'dataGrid',
  'class' => 'Ext_Grid',
  'extClass' => 'Grid',
  'name' => 'dataGrid',
  'state' => 
  array (
    'config' => 
    array (
      'store' => 'dataStore',
      'columnLines' => true,
      'viewConfig' => '{enableTextSelection:true}',
      'title' => '[js:]appLang.HISTORY_LOG',
      'flex' => 1.0,
      'isExtended' => false,
    ),
    'state' => 
    array (
      '_advancedPropertyValues' => 
      array (
        'groupHeaderTpl' => '{name} ({rows.length})',
        'startCollapsed' => false,
        'clicksToEdit' => 2,
        'rowBodyTpl' => '',
        'enableGroupingMenu' => true,
        'hideGroupedHeader' => false,
        'expander_rowbodytpl' => '',
        'checkboxSelection' => false,
        'editable' => false,
        'groupsummary' => true,
        'numberedRows' => false,
        'paging' => true,
        'rowexpander' => false,
        'grouping' => false,
        'summary' => false,
        'remoteRoot' => '',
      ),
    ),
    'columns' => 
    array (
      'date' => 
      array (
        'id' => 'date',
        'parent' => 0,
        'class' => 'Ext_Grid_Column_Date',
        'name' => 'date',
        'extClass' => 'Grid_Column_Date',
        'order' => 0,
        'state' => 
        array (
          'config' => 
          array (
            'format' => 'd.m.Y H:i:s',
            'align' => 'center',
            'dataIndex' => 'date',
            'text' => '[js:] appLang.DATE',
            'width' => 122.0,
          ),
        ),
      ),
      'object' => 
      array (
        'id' => 'object',
        'parent' => 0,
        'class' => 'Ext_Grid_Column',
        'name' => 'object',
        'extClass' => 'Grid_Column',
        'order' => 1,
        'state' => 
        array (
          'config' => 
          array (
            'dataIndex' => 'object_title',
            'text' => '[js:] appLang.OBJECT',
            'width' => 112.0,
          ),
        ),
      ),
      'recordid' => 
      array (
        'id' => 'recordid',
        'parent' => 0,
        'class' => 'Ext_Grid_Column',
        'name' => 'recordid',
        'extClass' => 'Grid_Column',
        'order' => 2,
        'state' => 
        array (
          'config' => 
          array (
            'align' => 'left',
            'dataIndex' => 'record_id',
            'text' => '[js:] appLang.RECORD_ID',
            'itemId' => 'recordid',
            'width' => 60.0,
          ),
        ),
      ),
      'type' => 
      array (
        'id' => 'type',
        'parent' => 0,
        'class' => 'Ext_Grid_Column',
        'name' => 'type',
        'extClass' => 'Grid_Column',
        'order' => 3,
        'state' => 
        array (
          'config' => 
          array (
            'align' => 'center',
            'dataIndex' => 'type',
            'renderer' => '',
            'text' => '[js:] appLang.ACTION',
            'width' => 85.0,
          ),
          'renderer' => 
          array (
            'type' => 'dictionary',
            'value' => 'log_operation',
          ),
        ),
      ),
      'user' => 
      array (
        'id' => 'user',
        'parent' => 0,
        'class' => 'Ext_Grid_Column',
        'name' => 'user',
        'extClass' => 'Grid_Column',
        'order' => 4,
        'state' => 
        array (
          'config' => 
          array (
            'dataIndex' => 'user_name',
            'text' => '[js:] appLang.USER',
            'itemId' => 'user',
            'width' => 135.0,
          ),
        ),
      ),
    ),
  ),
); 