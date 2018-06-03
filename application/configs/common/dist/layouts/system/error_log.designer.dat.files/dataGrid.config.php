<?php return array (
  'id' => 'dataGrid',
  'class' => 'Ext_Grid',
  'extClass' => 'Grid',
  'name' => 'dataGrid',
  'state' => 
  array (
    'config' => 
    array (
      'store' => '[new:] dataStore',
      'columnLines' => true,
      'viewConfig' => '{enableTextSelection: true}',
      'title' => '[js:] appLang.ERROR_LOG + \' : \' + appLang.HOME ',
      'isExtended' => true,
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
        'paging' => true,
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
            'format' => 'd.m.Y H:i',
            'dataIndex' => 'date',
            'text' => '[js:] appLang.DATE',
            'itemId' => 'date',
            'width' => 122.0,
          ),
        ),
      ),
      'name' => 
      array (
        'id' => 'name',
        'parent' => 0,
        'class' => 'Ext_Grid_Column',
        'name' => 'name',
        'extClass' => 'Grid_Column',
        'order' => 1,
        'state' => 
        array (
          'config' => 
          array (
            'dataIndex' => 'name',
            'text' => '[js:] appLang.SOURCE',
            'itemId' => 'name',
            'width' => 152.0,
          ),
        ),
      ),
      'message' => 
      array (
        'id' => 'message',
        'parent' => 0,
        'class' => 'Ext_Grid_Column',
        'name' => 'message',
        'extClass' => 'Grid_Column',
        'order' => 2,
        'state' => 
        array (
          'config' => 
          array (
            'dataIndex' => 'message',
            'renderer' => 'Ext_Component_Renderer_System_Multiline',
            'text' => '[js:] appLang.MESSAGE',
            'flex' => 1.0,
          ),
        ),
      ),
    ),
  ),
); 