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
      'package' => 
      array (
        'id' => 'package',
        'parent' => 0,
        'class' => 'Ext_Grid_Column',
        'name' => 'package',
        'extClass' => 'Grid_Column',
        'order' => 0,
        'state' => 
        array (
          'config' => 
          array (
            'dataIndex' => 'code',
            'text' => '[js:] appLang.NAME',
            'itemId' => 'package',
            'width' => 258.0,
          ),
        ),
      ),
      'title' => 
      array (
        'id' => 'title',
        'parent' => 0,
        'class' => 'Ext_Grid_Column',
        'name' => 'title',
        'extClass' => 'Grid_Column',
        'order' => 1,
        'state' => 
        array (
          'config' => 
          array (
            'dataIndex' => 'title',
            'text' => '[js:] appLang.TITLE',
            'flex' => 1.0,
            'width' => 200.0,
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
            'width' => 120.0,
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
            'width' => 120.0,
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
        'order' => 4,
        'state' => 
        array (
          'config' => 
          array (
            'format' => 'd.m.Y',
            'align' => 'center',
            'dataIndex' => 'date',
            'text' => '[js:] appLang.DATE',
            'itemId' => 'date',
            'width' => 120.0,
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
        'order' => 5,
        'state' => 
        array (
          'config' => 
          array (
            'dataIndex' => 'code',
            'itemId' => 'actions',
            'width' => 40.0,
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