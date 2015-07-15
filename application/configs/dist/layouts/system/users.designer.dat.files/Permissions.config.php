<?php return array (
  'id' => 'Permissions',
  'class' => 'Ext_Grid',
  'extClass' => 'Grid',
  'name' => 'Permissions',
  'state' => 
  array (
    'config' => 
    array (
      'store' => '[new:] permissionsStore',
      'columnLines' => true,
      'selModel' => ' {                 selType: \'cellmodel\'             }',
      'viewConfig' => '{                 stripeRows: true             }',
      'title' => '[js:] appLang.GROUP_PERMISSIONS',
      'frame' => false,
      'hidden' => false,
      'isExtended' => true,
      'defineOnly' => true,
    ),
    'state' => 
    array (
      '_advancedPropertyValues' => 
      array (
        'groupHeaderTpl' => '{name} ({rows.length})',
        'startCollapsed' => false,
        'clicksToEdit' => 1,
        'rowBodyTpl' => '',
        'enableGroupingMenu' => true,
        'hideGroupedHeader' => false,
        'expander_rowbodytpl' => '',
        'checkboxSelection' => false,
        'editable' => true,
        'groupsummary' => false,
        'numberedRows' => false,
        'paging' => false,
        'rowexpander' => false,
        'grouping' => false,
        'summary' => false,
        'remoteRoot' => '',
      ),
    ),
    'columns' => 
    array (
      'module' => 
      array (
        'id' => 'module',
        'parent' => 0,
        'class' => 'Ext_Grid_Column',
        'extClass' => 'Grid_Column',
        'order' => 0,
        'name' => 'module',
        'state' => 
        array (
          'config' => 
          array (
            'dataIndex' => 'module',
            'text' => '[js:] appLang.MODULE',
            'itemId' => 'module',
            'width' => 227,
            'editable' => false,
          ),
        ),
      ),
      'view' => 
      array (
        'id' => 'view',
        'parent' => 0,
        'class' => 'Ext_Grid_Column_Check',
        'extClass' => 'Grid_Column_Check',
        'order' => 2,
        'name' => NULL,
        'state' => 
        array (
          'config' => 
          array (
            'dataIndex' => 'view',
            'renderer' => 'Ext_Component_Renderer_System_Checkbox',
            'text' => '[js:] appLang.VIEW',
            'itemId' => 'view',
          ),
        ),
      ),
      'edit' => 
      array (
        'id' => 'edit',
        'parent' => 0,
        'class' => 'Ext_Grid_Column_Check',
        'extClass' => 'Grid_Column_Check',
        'order' => 3,
        'name' => NULL,
        'state' => 
        array (
          'config' => 
          array (
            'dataIndex' => 'edit',
            'renderer' => 'Ext_Component_Renderer_System_Checkbox',
            'text' => '[js:] appLang.EDIT',
            'itemId' => 'edit',
          ),
        ),
      ),
      'delete' => 
      array (
        'id' => 'delete',
        'parent' => 0,
        'class' => 'Ext_Grid_Column_Check',
        'extClass' => 'Grid_Column_Check',
        'order' => 4,
        'name' => NULL,
        'state' => 
        array (
          'config' => 
          array (
            'dataIndex' => 'delete',
            'renderer' => 'Ext_Component_Renderer_System_Checkbox',
            'text' => '[js:] appLang.DELETE',
            'itemId' => 'delete',
          ),
        ),
      ),
      'allcol' => 
      array (
        'id' => 'allcol',
        'parent' => '0',
        'class' => 'Ext_Grid_Column',
        'extClass' => 'Grid_Column',
        'order' => 1,
        'name' => 'allcol',
        'state' => 
        array (
          'config' => 
          array (
            'align' => 'center',
            'renderer' => 'Ext_Component_Renderer_System_User_Allchecked',
            'scope' => 'me',
            'text' => '[js:] appLang.ALL',
            'itemId' => 'allcol',
            'width' => 60,
          ),
        ),
      ),
      'publish' => 
      array (
        'id' => 'publish',
        'parent' => 0,
        'class' => 'Ext_Grid_Column',
        'extClass' => 'Grid_Column',
        'order' => 5,
        'name' => 'publish',
        'state' => 
        array (
          'config' => 
          array (
            'align' => 'center',
            'dataIndex' => 'publish',
            'renderer' => 'Ext_Component_Renderer_System_User_Publish',
            'text' => '[js:] appLang.TO_PUBLISH',
            'itemId' => 'publish',
          ),
        ),
      ),
    ),
  ),
); 