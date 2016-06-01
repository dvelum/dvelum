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
      'viewConfig' => '{enableTextSelection: true}',
      'title' => '[js:] appLang.FILESTORAGE+\' :: \' + appLang.HOME',
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
        'checkboxSelection' => false,
        'editable' => false,
        'groupsummary' => false,
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
      'id' => 
      array (
        'id' => 'id',
        'parent' => 0,
        'class' => 'Ext_Grid_Column',
        'name' => 'id',
        'extClass' => 'Grid_Column',
        'order' => 0,
        'state' => 
        array (
          'config' => 
          array (
            'align' => 'right',
            'dataIndex' => 'id',
            'text' => '[js:] appLang.FILE_ID',
            'itemId' => 'id',
            'width' => 69.0,
          ),
        ),
      ),
      'date' => 
      array (
        'id' => 'date',
        'parent' => 0,
        'class' => 'Ext_Grid_Column_Date',
        'name' => 'date',
        'extClass' => 'Grid_Column_Date',
        'order' => 1,
        'state' => 
        array (
          'config' => 
          array (
            'format' => 'd.m.Y H:i',
            'align' => 'center',
            'dataIndex' => 'date',
            'text' => '[js:] appLang.UPLOAD_DATE',
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
        'order' => 2,
        'state' => 
        array (
          'config' => 
          array (
            'dataIndex' => 'name',
            'text' => '[js:] appLang.FILE_NAME',
            'width' => 297.0,
          ),
        ),
      ),
      'size' => 
      array (
        'id' => 'size',
        'parent' => 0,
        'class' => 'Ext_Grid_Column',
        'name' => NULL,
        'extClass' => 'Grid_Column',
        'order' => 3,
        'state' => 
        array (
          'config' => 
          array (
            'align' => 'right',
            'dataIndex' => 'size',
            'text' => '[js:] appLang.SIZE_MB',
          ),
        ),
      ),
      'user_name' => 
      array (
        'id' => 'user_name',
        'parent' => 0,
        'class' => 'Ext_Grid_Column',
        'name' => 'user_name',
        'extClass' => 'Grid_Column',
        'order' => 4,
        'state' => 
        array (
          'config' => 
          array (
            'dataIndex' => 'user_name',
            'sortable' => false,
            'text' => '[js:] appLang.UPLOADED_BY',
            'itemId' => 'user_name',
            'width' => 168.0,
          ),
        ),
      ),
      'action' => 
      array (
        'id' => 'action',
        'parent' => 0,
        'class' => 'Ext_Grid_Column_Action',
        'name' => NULL,
        'extClass' => 'Grid_Column_Action',
        'order' => 5,
        'state' => 
        array (
          'config' => 
          array (
            'align' => 'center',
            'text' => '[js:] appLang.ACTIONS',
            'itemId' => 'action',
            'width' => 64.0,
          ),
          'actions' => 
          array (
            'dataGrid_action_download' => 
            array (
              'id' => 'dataGrid_action_download',
              'parent' => 0,
              'name' => 'dataGrid_action_download',
              'class' => 'Ext_Grid_Column_Action_Button',
              'extClass' => 'Grid_Column_Action_Button',
              'order' => false,
              'state' => 
              array (
                'config' => 
                array (
                  'icon' => '[%wroot%]i/system/database-export.png',
                  'text' => 'dataGrid_action_download',
                  'tooltip' => '[js:] appLang.DOWNLOAD',
                ),
              ),
            ),
            'dataGrid_action_delete' => 
            array (
              'id' => 'dataGrid_action_delete',
              'parent' => 0,
              'name' => 'dataGrid_action_delete',
              'class' => 'Ext_Grid_Column_Action_Button',
              'extClass' => 'Grid_Column_Action_Button',
              'order' => 1,
              'state' => 
              array (
                'config' => 
                array (
                  'isDisabled' => 'function(){return !this.canDelete;}',
                  'icon' => '[%wroot%]i/system/delete.gif',
                  'text' => 'dataGrid_action_delete',
                  'tooltip' => '[js:] appLang.DELETE_ITEM',
                ),
              ),
            ),
          ),
        ),
      ),
    ),
  ),
); 