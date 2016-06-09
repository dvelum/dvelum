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
      'title' => '[js:] appLang.EXTERNAL_MODULES',
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
      ),
    ),
    'columns' => 
    array (
      'name' => 
      array (
        'id' => 'name',
        'parent' => 0,
        'class' => 'Ext_Grid_Column',
        'name' => 'name',
        'extClass' => 'Grid_Column',
        'order' => 0,
        'state' => 
        array (
          'config' => 
          array (
            'dataIndex' => 'name',
            'text' => '[js:] appLang.TITLE',
            'width' => 177.0,
          ),
        ),
      ),
      'enabled' => 
      array (
        'id' => 'enabled',
        'parent' => 0,
        'class' => 'Ext_Grid_Column',
        'name' => 'enabled',
        'extClass' => 'Grid_Column',
        'order' => 1,
        'state' => 
        array (
          'config' => 
          array (
            'align' => 'center',
            'dataIndex' => 'enabled',
            'renderer' => '',
            'text' => '[js:] appLang.ENABLED',
            'width' => 60.0,
          ),
          'renderer' => 
          array (
            'type' => 'adapter',
            'value' => 'Ext_Component_Renderer_System_Checkbox',
          ),
        ),
      ),
      'version' => 
      array (
        'id' => 'version',
        'parent' => 0,
        'class' => 'Ext_Grid_Column',
        'name' => 'version',
        'extClass' => 'Grid_Column',
        'order' => 2,
        'state' => 
        array (
          'config' => 
          array (
            'align' => 'center',
            'dataIndex' => 'version',
            'text' => '[js:] appLang.VERSION',
            'width' => 51.0,
          ),
        ),
      ),
      'author' => 
      array (
        'id' => 'author',
        'parent' => 0,
        'class' => 'Ext_Grid_Column',
        'name' => 'author',
        'extClass' => 'Grid_Column',
        'order' => 3,
        'state' => 
        array (
          'config' => 
          array (
            'dataIndex' => 'author',
            'text' => '[js:] appLang.AUTHOR',
          ),
        ),
      ),
      'installed' => 
      array (
        'id' => 'installed',
        'parent' => 0,
        'class' => 'Ext_Grid_Column_Boolean',
        'name' => 'installed',
        'extClass' => 'Grid_Column_Boolean',
        'order' => 4,
        'state' => 
        array (
          'config' => 
          array (
            'align' => 'center',
            'dataIndex' => 'installed',
            'renderer' => '',
            'text' => '[js:] externalsLang.installed',
            'width' => 60.0,
          ),
          'renderer' => 
          array (
            'type' => 'adapter',
            'value' => 'Ext_Component_Renderer_System_Checkbox',
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
            'align' => 'center',
            'dataIndex' => 'id',
            'text' => '[js:] appLang.ACTIONS',
            'itemId' => 'actions',
            'width' => 81.0,
          ),
          'actions' => 
          array (
            'dataGrid_action_reinstallAction' => 
            array (
              'id' => 'dataGrid_action_reinstallAction',
              'parent' => 0,
              'name' => 'dataGrid_action_reinstallAction',
              'class' => 'Ext_Grid_Column_Action_Button',
              'extClass' => 'Grid_Column_Action_Button',
              'order' => false,
              'state' => 
              array (
                'config' => 
                array (
                  'icon' => '[%wroot%]i/refresh.png',
                  'text' => 'dataGrid_action_reinstallAction',
                  'tooltip' => '[js:] appLang.REINSTALL',
                ),
              ),
            ),
            'dataGrid_action_action_enable' => 
            array (
              'id' => 'dataGrid_action_action_enable',
              'parent' => 0,
              'name' => 'dataGrid_action_action_enable',
              'class' => 'Ext_Grid_Column_Action_Button',
              'extClass' => 'Grid_Column_Action_Button',
              'order' => 1,
              'state' => 
              array (
                'config' => 
                array (
                  'icon' => '[%wroot%]i/system/yes.gif',
                  'text' => 'dataGrid_action_action_enable',
                  'tooltip' => '[js:] appLang.ENABLE',
                ),
              ),
            ),
            'dataGrid_action_action_disable' => 
            array (
              'id' => 'dataGrid_action_action_disable',
              'parent' => 0,
              'name' => 'dataGrid_action_action_disable',
              'class' => 'Ext_Grid_Column_Action_Button',
              'extClass' => 'Grid_Column_Action_Button',
              'order' => 2,
              'state' => 
              array (
                'config' => 
                array (
                  'icon' => '[%wroot%]i/system/no.png',
                  'text' => 'dataGrid_action_action_disable',
                  'tooltip' => '[js:] appLang.DISABLE',
                ),
              ),
            ),
            'dataGrid_action_action_uninstall' => 
            array (
              'id' => 'dataGrid_action_action_uninstall',
              'parent' => 0,
              'name' => 'dataGrid_action_action_uninstall',
              'class' => 'Ext_Grid_Column_Action_Button',
              'extClass' => 'Grid_Column_Action_Button',
              'order' => 3,
              'state' => 
              array (
                'config' => 
                array (
                  'icon' => '[%wroot%]i/system/delete.gif',
                  'text' => 'dataGrid_action_action_uninstall',
                  'tooltip' => '[js:] appLang.UNINSTALL',
                ),
              ),
            ),
          ),
        ),
      ),
    ),
  ),
); 