<?php return array (
  'id' => 'individualPermissions',
  'class' => 'Ext_Grid',
  'extClass' => 'Grid',
  'name' => 'individualPermissions',
  'state' => 
  array (
    'config' => 
    array (
      'store' => '[new:] individualPermissionsStore',
      'columnLines' => true,
      'title' => '[js:] appLang.USER_PERMISSIONS',
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
            'text' => '[js:] appLang.MODULE',
            'width' => 145.0,
          ),
        ),
      ),
      'checkallcol' => 
      array (
        'id' => 'checkallcol',
        'parent' => 0,
        'class' => 'Ext_Grid_Column',
        'name' => 'checkallcol',
        'extClass' => 'Grid_Column',
        'order' => 1,
        'state' => 
        array (
          'config' => 
          array (
            'align' => 'center',
            'renderer' => '',
            'text' => '[js:] appLang.ALL',
            'itemId' => 'allcol',
            'width' => 64.0,
          ),
          'renderer' => 
          array (
            'type' => 'adapter',
            'value' => 'Ext_Component_Renderer_System_User_Allchecked',
          ),
        ),
      ),
      'view' => 
      array (
        'id' => 'view',
        'parent' => 0,
        'class' => 'Ext_Grid_Column_Boolean',
        'name' => NULL,
        'extClass' => 'Grid_Column_Boolean',
        'order' => 2,
        'state' => 
        array (
          'config' => 
          array (
            'align' => 'center',
            'dataIndex' => 'view',
            'renderer' => '',
            'text' => '[js:] appLang.VIEW',
            'itemId' => 'view',
            'width' => 70.0,
          ),
          'renderer' => 
          array (
            'type' => 'adapter',
            'value' => 'Ext_Component_Renderer_System_User_Check',
          ),
        ),
      ),
      'edit' => 
      array (
        'id' => 'edit',
        'parent' => 0,
        'class' => 'Ext_Grid_Column_Boolean',
        'name' => NULL,
        'extClass' => 'Grid_Column_Boolean',
        'order' => 3,
        'state' => 
        array (
          'config' => 
          array (
            'align' => 'center',
            'dataIndex' => 'edit',
            'renderer' => '',
            'text' => '[js:] appLang.EDIT',
            'itemId' => 'edit',
            'width' => 70.0,
          ),
          'renderer' => 
          array (
            'type' => 'adapter',
            'value' => 'Ext_Component_Renderer_System_User_Check',
          ),
        ),
      ),
      'delete' => 
      array (
        'id' => 'delete',
        'parent' => 0,
        'class' => 'Ext_Grid_Column_Boolean',
        'name' => NULL,
        'extClass' => 'Grid_Column_Boolean',
        'order' => 4,
        'state' => 
        array (
          'config' => 
          array (
            'align' => 'center',
            'dataIndex' => 'delete',
            'renderer' => '',
            'text' => '[js:] appLang.DELETE',
            'itemId' => 'delete',
            'width' => 70.0,
          ),
          'renderer' => 
          array (
            'type' => 'adapter',
            'value' => 'Ext_Component_Renderer_System_User_Check',
          ),
        ),
      ),
      'publish' => 
      array (
        'id' => 'publish',
        'parent' => 0,
        'class' => 'Ext_Grid_Column_Boolean',
        'name' => NULL,
        'extClass' => 'Grid_Column_Boolean',
        'order' => 5,
        'state' => 
        array (
          'config' => 
          array (
            'align' => 'center',
            'dataIndex' => 'publish',
            'renderer' => '',
            'text' => '[js:] appLang.TO_PUBLISH',
            'itemId' => 'publish',
            'width' => 70.0,
          ),
          'renderer' => 
          array (
            'type' => 'adapter',
            'value' => 'Ext_Component_Renderer_System_User_Check_Publish',
          ),
        ),
      ),
      'only_own' => 
      array (
        'id' => 'only_own',
        'parent' => 0,
        'class' => 'Ext_Grid_Column_Boolean',
        'name' => NULL,
        'extClass' => 'Grid_Column_Boolean',
        'order' => 6,
        'state' => 
        array (
          'config' => 
          array (
            'align' => 'center',
            'dataIndex' => 'only_own',
            'editor' => '',
            'renderer' => '',
            'text' => '[js:] appLang.ONLY_OWN',
            'itemId' => 'only_own',
            'width' => 108.0,
          ),
          'renderer' => 
          array (
            'type' => 'adapter',
            'value' => 'Ext_Component_Renderer_System_User_Check_Publish',
          ),
          'editor' => 
          array (
            'extClass' => 'Form_Field_Text',
            'name' => 'individualPermissions__editor',
            'state' => 
            array (
              'config' => 
              array (
              ),
            ),
          ),
        ),
      ),
    ),
  ),
); 