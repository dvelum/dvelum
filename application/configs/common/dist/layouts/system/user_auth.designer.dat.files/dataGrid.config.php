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
      'title' => '[js:] appLang.AUTH + \' :: \' + appLang.HOME',
      'minHeight' => 400.0,
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
            'dataIndex' => 'id',
            'text' => '[js:] appLang.PRIMARY_KEY',
            'hidden' => true,
            'itemId' => 'id',
          ),
        ),
      ),
      'user' => 
      array (
        'id' => 'user',
        'parent' => 0,
        'class' => 'Ext_Grid_Column',
        'name' => NULL,
        'extClass' => 'Grid_Column',
        'order' => 1,
        'state' => 
        array (
          'config' => 
          array (
            'dataIndex' => 'user',
            'text' => '[js:] appLang.USER',
          ),
        ),
      ),
      'user_login' => 
      array (
        'id' => 'user_login',
        'parent' => 0,
        'class' => 'Ext_Grid_Column',
        'name' => 'user_login',
        'extClass' => 'Grid_Column',
        'order' => 2,
        'state' => 
        array (
          'config' => 
          array (
            'dataIndex' => 'login',
            'text' => '[js:] appLang.LOGIN',
            'itemId' => 'user_login',
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
            'dataIndex' => 'type',
            'renderer' => '',
            'text' => '[js:] appLang.AUTH_TYPE',
            'itemId' => 'type',
          ),
          'renderer' => 
          array (
            'type' => 'dictionary',
            'value' => 'auth_type',
          ),
        ),
      ),
      'dataGrid_actions' => 
      array (
        'id' => 'dataGrid_actions',
        'parent' => 0,
        'class' => 'Ext_Grid_Column_Action',
        'name' => 'dataGrid_actions',
        'extClass' => 'Grid_Column_Action',
        'order' => 4,
        'state' => 
        array (
          'config' => 
          array (
            'align' => 'center',
            'text' => '[js:] appLang.ACTIONS',
            'width' => 50.0,
          ),
          'actions' => 
          array (
            'dataGrid_actions_delete' => 
            array (
              'id' => 'dataGrid_actions_delete',
              'parent' => 0,
              'name' => 'dataGrid_actions_delete',
              'class' => 'Ext_Grid_Column_Action_Button',
              'extClass' => 'Grid_Column_Action_Button',
              'order' => false,
              'state' => 
              array (
                'config' => 
                array (
                  'isDisabled' => 'function(){return !this.canDelete;}',
                  'icon' => '[%wroot%]i/system/delete.png',
                  'text' => 'dg_action_delete',
                  'tooltip' => '[js:] appLang.DELETE',
                ),
              ),
            ),
          ),
        ),
      ),
    ),
  ),
); 