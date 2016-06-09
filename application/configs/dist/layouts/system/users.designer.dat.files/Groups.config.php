<?php return array (
  'id' => 'Groups',
  'class' => 'Ext_Grid',
  'extClass' => 'Grid',
  'name' => 'Groups',
  'state' => 
  array (
    'config' => 
    array (
      'store' => '[new:] groupsStore',
      'columnLines' => true,
      'title' => '[js:] appLang.GROUPS',
      'frame' => false,
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
            'text' => '[js:] appLang.NAME',
            'flex' => 1.0,
            'itemId' => 'title',
          ),
        ),
      ),
      'system' => 
      array (
        'id' => 'system',
        'parent' => 0,
        'class' => 'Ext_Grid_Column',
        'name' => 'system',
        'extClass' => 'Grid_Column',
        'order' => 1,
        'state' => 
        array (
          'config' => 
          array (
            'align' => 'center',
            'dataIndex' => 'system',
            'renderer' => 'Ext_Component_Renderer_System_Checkbox',
            'text' => '[js:] appLang.SYSTEM',
            'itemId' => 'system',
            'width' => 60.0,
          ),
        ),
      ),
      'delete' => 
      array (
        'id' => 'delete',
        'parent' => 0,
        'class' => 'Ext_Grid_Column_Action',
        'name' => NULL,
        'extClass' => 'Grid_Column_Action',
        'order' => 2,
        'state' => 
        array (
          'config' => 
          array (
            'align' => 'center',
            'itemId' => 'delete',
            'width' => 30.0,
          ),
          'actions' => 
          array (
            'Groups_action_delete' => 
            array (
              'id' => 'Groups_action_delete',
              'parent' => 0,
              'name' => 'Groups_action_delete',
              'class' => 'Ext_Grid_Column_Action_Button',
              'extClass' => 'Grid_Column_Action_Button',
              'order' => false,
              'state' => 
              array (
                'config' => 
                array (
                  'isDisabled' => 'function(view , rowIndex , colIndex , item , record){  if (!me.canDelete){      return true;  }  if (record.get(\'system\') == true) {     return true;  }  return false; }',
                  'icon' => '[%wroot%]i/system/delete.gif',
                  'text' => 'Groups_action_delete',
                  'tooltip' => '[js:] appLang.DELETE_ITEM',
                  'disabled' => false,
                ),
              ),
            ),
          ),
        ),
      ),
    ),
  ),
); 