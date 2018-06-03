<?php return array (
  'id' => 'rbItemsGrid',
  'class' => 'Ext_Grid',
  'extClass' => 'Grid',
  'name' => 'rbItemsGrid',
  'state' => 
  array (
    'config' => 
    array (
      'store' => 'repoItemsStore',
      'columnLines' => true,
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
      'title' => 
      array (
        'id' => 'title',
        'parent' => 0,
        'class' => 'Ext_Grid_Column',
        'name' => 'title',
        'extClass' => 'Grid_Column',
        'order' => 0,
        'state' => 
        array (
          'config' => 
          array (
            'dataIndex' => 'title',
            'text' => '[js:] appLang.TITLE',
            'flex' => 0.0,
            'width' => 200.0,
          ),
        ),
      ),
      'url' => 
      array (
        'id' => 'url',
        'parent' => 0,
        'class' => 'Ext_Grid_Column',
        'name' => 'url',
        'extClass' => 'Grid_Column',
        'order' => 1,
        'state' => 
        array (
          'config' => 
          array (
            'dataIndex' => 'page',
            'renderer' => '',
            'text' => '[js:] externalsLang.app_page',
            'itemId' => 'url',
            'width' => 350.0,
          ),
          'renderer' => 
          array (
            'type' => 'adapter',
            'value' => 'Ext_Component_Renderer_System_Url',
          ),
        ),
      ),
      'downloads' => 
      array (
        'id' => 'downloads',
        'parent' => 0,
        'class' => 'Ext_Grid_Column',
        'name' => 'downloads',
        'extClass' => 'Grid_Column',
        'order' => 2,
        'state' => 
        array (
          'config' => 
          array (
            'align' => 'center',
            'dataIndex' => 'downloads',
            'text' => '[js:] externalsLang.downloads',
            'itemId' => 'downloads',
            'width' => 78.0,
          ),
        ),
      ),
      'last_version_num' => 
      array (
        'id' => 'last_version_num',
        'parent' => 0,
        'class' => 'Ext_Grid_Column',
        'name' => 'last_version_num',
        'extClass' => 'Grid_Column',
        'order' => 3,
        'state' => 
        array (
          'config' => 
          array (
            'align' => 'center',
            'dataIndex' => 'number',
            'text' => '[js:] appLang.LAST_VERSION',
            'itemId' => 'last_version_num',
            'width' => 80.0,
          ),
        ),
      ),
      'size' => 
      array (
        'id' => 'size',
        'parent' => 0,
        'class' => 'Ext_Grid_Column',
        'name' => 'size',
        'extClass' => 'Grid_Column',
        'order' => 4,
        'state' => 
        array (
          'config' => 
          array (
            'align' => 'right',
            'dataIndex' => 'size',
            'text' => '[js:] appLang.SIZE',
            'itemId' => 'size',
            'width' => 71.0,
          ),
        ),
      ),
      'date' => 
      array (
        'id' => 'date',
        'parent' => 0,
        'class' => 'Ext_Grid_Column_Date',
        'name' => NULL,
        'extClass' => 'Grid_Column_Date',
        'order' => 5,
        'state' => 
        array (
          'config' => 
          array (
            'format' => 'd.m.Y',
            'align' => 'center',
            'dataIndex' => 'date',
            'text' => '[js:] appLang.DATE',
            'itemId' => 'date',
            'width' => 78.0,
          ),
        ),
      ),
      'actions' => 
      array (
        'id' => 'actions',
        'parent' => 0,
        'class' => 'Ext_Grid_Column_Action',
        'name' => NULL,
        'extClass' => 'Grid_Column_Action',
        'order' => 6,
        'state' => 
        array (
          'config' => 
          array (
            'dataIndex' => 'code',
            'itemId' => 'actions',
            'width' => 20.0,
          ),
          'actions' => 
          array (
            'rbItemsGrid_action_downloadLatest' => 
            array (
              'id' => 'rbItemsGrid_action_downloadLatest',
              'parent' => 0,
              'name' => 'rbItemsGrid_action_downloadLatest',
              'class' => 'Ext_Grid_Column_Action_Button',
              'extClass' => 'Grid_Column_Action_Button',
              'order' => false,
              'state' => 
              array (
                'config' => 
                array (
                  'icon' => '[%wroot%]i/system/download.png',
                  'text' => 'rbItemsGrid_action_downloadLatest',
                  'tooltip' => '[js:] externalsLang.download_latest',
                ),
              ),
            ),
          ),
        ),
      ),
    ),
  ),
); 