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
      'date' => 
      array (
        'id' => 'date',
        'parent' => 0,
        'class' => 'Ext_Grid_Column_Date',
        'extClass' => 'Grid_Column_Date',
        'order' => false,
        'name' => 'date',
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
        'extClass' => 'Grid_Column',
        'order' => 1,
        'name' => 'name',
        'state' => 
        array (
          'config' => 
          array (
            'dataIndex' => 'name',
            'text' => '[js:] appLang.FILE_NAME',
            'width' => 297,
          ),
        ),
      ),
      'size' => 
      array (
        'id' => 'size',
        'parent' => 0,
        'class' => 'Ext_Grid_Column',
        'extClass' => 'Grid_Column',
        'order' => 2,
        'name' => NULL,
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
        'extClass' => 'Grid_Column',
        'order' => 3,
        'name' => 'user_name',
        'state' => 
        array (
          'config' => 
          array (
            'dataIndex' => 'user_name',
            'sortable' => false,
            'text' => '[js:] appLang.UPLOADED_BY',
            'itemId' => 'user_name',
            'width' => 168,
          ),
        ),
      ),
      'id' => 
      array (
        'id' => 'id',
        'parent' => 0,
        'class' => 'Ext_Grid_Column',
        'extClass' => 'Grid_Column',
        'order' => 0,
        'name' => 'id',
        'state' => 
        array (
          'config' => 
          array (
            'align' => 'right',
            'dataIndex' => 'id',
            'text' => '[js:] appLang.FILE_ID',
            'itemId' => 'id',
            'width' => 69,
          ),
        ),
      ),
      'action' => 
      array (
        'id' => 'action',
        'parent' => 0,
        'class' => 'Ext_Grid_Column_Action',
        'extClass' => 'Grid_Column_Action',
        'order' => 5,
        'name' => NULL,
        'state' => 
        array (
          'config' => 
          array (
            'align' => 'center',
            'text' => '[js:] appLang.ACTIONS',
            'itemId' => 'action',
            'width' => 64,
          ),
          'state' => 
          array (
            '_isExtended' => false,
          ),
          'actions' => 
          array (
            'dataGrid_action_download' => 
            array (
              'id' => 'dataGrid_action_download',
              'parent' => 0,
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