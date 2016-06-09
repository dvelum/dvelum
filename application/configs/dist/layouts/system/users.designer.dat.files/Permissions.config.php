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
            'text' => '[js:] appLang.MODULE ',
            'itemId' => 'title',
            'width' => 189.0,
          ),
        ),
      ),
      'allcol' => 
      array (
        'id' => 'allcol',
        'parent' => '0',
        'class' => 'Ext_Grid_Column',
        'name' => 'allcol',
        'extClass' => 'Grid_Column',
        'order' => 1,
        'state' => 
        array (
          'config' => 
          array (
            'align' => 'center',
            'renderer' => 'Ext_Component_Renderer_System_User_Allchecked',
            'scope' => 'me',
            'text' => '[js:] appLang.ALL',
            'itemId' => 'allcol',
            'width' => 60.0,
          ),
        ),
      ),
      'view' => 
      array (
        'id' => 'view',
        'parent' => 0,
        'class' => 'Ext_Grid_Column_Check',
        'name' => NULL,
        'extClass' => 'Grid_Column_Check',
        'order' => 2,
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
        'name' => NULL,
        'extClass' => 'Grid_Column_Check',
        'order' => 3,
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
        'name' => NULL,
        'extClass' => 'Grid_Column_Check',
        'order' => 4,
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
      'publish' => 
      array (
        'id' => 'publish',
        'parent' => 0,
        'class' => 'Ext_Grid_Column_Check',
        'name' => NULL,
        'extClass' => 'Grid_Column_Check',
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
          ),
          'renderer' => 
          array (
            'type' => 'adapter',
            'value' => 'Ext_Component_Renderer_System_User_Publish',
          ),
        ),
      ),
      'only_own' => 
      array (
        'id' => 'only_own',
        'parent' => 0,
        'class' => 'Ext_Grid_Column_Check',
        'name' => NULL,
        'extClass' => 'Grid_Column_Check',
        'order' => 6,
        'state' => 
        array (
          'config' => 
          array (
            'align' => 'center',
            'dataIndex' => 'only_own',
            'renderer' => '',
            'text' => '[js:] appLang.ONLY_OWN',
            'itemId' => 'only_own',
            'width' => 117.0,
          ),
          'renderer' => 
          array (
            'type' => 'adapter',
            'value' => 'Ext_Component_Renderer_System_User_Publish',
          ),
        ),
      ),
    ),
  ),
); 