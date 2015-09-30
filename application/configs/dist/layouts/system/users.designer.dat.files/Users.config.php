<?php return array (
  'id' => 'Users',
  'class' => 'Ext_Grid',
  'extClass' => 'Grid',
  'name' => 'Users',
  'state' => 
  array (
    'config' => 
    array (
      'store' => 'usersStoreInstance',
      'columnLines' => true,
      'viewConfig' => '{enableTextSelection: true}',
      'title' => '[js:] appLang.USERS +1',
      'isExtended' => true,
      'defineOnly' => true,
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
            'align' => 'left',
            'dataIndex' => 'name',
            'text' => '[js:] appLang.NAME',
            'flex' => 1,
          ),
        ),
      ),
      'admin' => 
      array (
        'id' => 'admin',
        'parent' => 0,
        'class' => 'Ext_Grid_Column_Boolean',
        'extClass' => 'Grid_Column_Boolean',
        'order' => 5,
        'name' => 'admin',
        'state' => 
        array (
          'config' => 
          array (
            'align' => 'center',
            'dataIndex' => 'admin',
            'renderer' => 'Ext_Component_Renderer_System_Checkbox',
            'text' => '[js:] appLang.ADMIN',
            'width' => 86,
          ),
        ),
      ),
      'enabled' => 
      array (
        'id' => 'enabled',
        'parent' => 0,
        'class' => 'Ext_Grid_Column_Boolean',
        'extClass' => 'Grid_Column_Boolean',
        'order' => 7,
        'name' => 'enabled',
        'state' => 
        array (
          'config' => 
          array (
            'align' => 'center',
            'dataIndex' => 'enabled',
            'renderer' => 'Ext_Component_Renderer_System_Checkbox',
            'text' => '[js:] appLang.ENABLED',
            'width' => 79,
          ),
        ),
      ),
      'group_title' => 
      array (
        'id' => 'group_title',
        'parent' => 0,
        'class' => 'Ext_Grid_Column',
        'extClass' => 'Grid_Column',
        'order' => 2,
        'name' => 'group_title',
        'state' => 
        array (
          'config' => 
          array (
            'dataIndex' => 'group_title',
            'text' => '[js:] appLang.GROUP',
            'width' => 158,
          ),
        ),
      ),
      'email' => 
      array (
        'id' => 'email',
        'parent' => 0,
        'class' => 'Ext_Grid_Column',
        'extClass' => 'Grid_Column',
        'order' => 4,
        'name' => 'email',
        'state' => 
        array (
          'config' => 
          array (
            'dataIndex' => 'email',
            'text' => '[js:] appLang.EMAIL',
            'width' => 129,
          ),
        ),
      ),
      'login' => 
      array (
        'id' => 'login',
        'parent' => 0,
        'class' => 'Ext_Grid_Column',
        'extClass' => 'Grid_Column',
        'order' => 3,
        'name' => 'login',
        'state' => 
        array (
          'config' => 
          array (
            'dataIndex' => 'login',
            'text' => '[js:] appLang.LOGIN',
            'width' => 133,
          ),
        ),
      ),
      'preaction' => 
      array (
        'id' => 'preaction',
        'parent' => 0,
        'class' => 'Ext_Grid_Column_Action',
        'extClass' => 'Grid_Column_Action',
        'order' => 0,
        'name' => NULL,
        'state' => 
        array (
          'config' => 
          array (
            'align' => 'center',
            'itemId' => 'preaction',
            'width' => 40,
          ),
          'actions' => 
          array (
            'Users_action_edit' => 
            array (
              'id' => 'Users_action_edit',
              'parent' => 0,
              'class' => 'Ext_Grid_Column_Action_Button',
              'extClass' => 'Grid_Column_Action_Button',
              'order' => false,
              'state' => 
              array (
                'config' => 
                array (
                  'isDisabled' => 'function(view,rowIndex,colIndex,item,record){return !this.canEdit;}',
                  'icon' => '[%wroot%]i/system/edit.png',
                  'scope' => 'this',
                  'text' => 'Users_action_edit',
                  'tooltip' => '[js:] appLang.EDIT',
                ),
              ),
            ),
          ),
        ),
      ),
      'postaction' => 
      array (
        'id' => 'postaction',
        'parent' => 0,
        'class' => 'Ext_Grid_Column_Action',
        'extClass' => 'Grid_Column_Action',
        'order' => 7,
        'name' => NULL,
        'state' => 
        array (
          'config' => 
          array (
            'align' => 'center',
            'itemId' => 'postaction',
            'width' => 40,
          ),
          'actions' => 
          array (
            'Users_action_delete' => 
            array (
              'id' => 'Users_action_delete',
              'parent' => 0,
              'class' => 'Ext_Grid_Column_Action_Button',
              'extClass' => 'Grid_Column_Action_Button',
              'order' => false,
              'state' => 
              array (
                'config' => 
                array (
                  'isDisabled' => 'function(view,rowIndex,colIndex,item,record){return !this.canDelete;}',
                  'icon' => '[%wroot%]i/system/delete.gif',
                  'scope' => 'this',
                  'text' => 'Users_action_delete',
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