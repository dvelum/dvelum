<?php return array (
  'id' => 'dataGrid',
  'class' => 'Ext_Grid',
  'extClass' => 'Grid',
  'name' => 'dataGrid',
  'state' => 
  array (
    'config' => 
    array (
      'store' => '[new:] localizationStore',
      'columnLines' => true,
      'viewConfig' => '{enableTextSelection: true}',
      'title' => '[js:] appLang.LOCALIZATION',
      'isExtended' => true,
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
            'dataIndex' => 'key',
            'text' => '[js:] appLang.KEY',
            'itemId' => 'id',
            'width' => 228.0,
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
            'editor' => '',
            'renderer' => 'Ext_Component_Renderer_System_Multiline',
            'text' => '[js:] appLang.VALUE',
            'flex' => 1.0,
            'itemId' => 'title',
            'width' => 1003.0,
          ),
          'editor' => 
          array (
            'extClass' => 'Form_Field_Textarea',
            'name' => 'dataGrid_title_editor',
            'state' => 
            array (
              'config' => 
              array (
                'height' => 100.0,
              ),
            ),
          ),
        ),
      ),
      'sync' => 
      array (
        'id' => 'sync',
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
            'dataIndex' => 'sync',
            'renderer' => 'Ext_Component_Renderer_System_Checkbox',
            'text' => '[js:] appLang.SYNCHRONIZED',
            'itemId' => 'sync',
            'width' => 94.0,
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
        'order' => 3,
        'state' => 
        array (
          'config' => 
          array (
            'align' => 'center',
            'dataIndex' => 'id',
            'itemId' => 'actions',
            'width' => 40.0,
          ),
          'actions' => 
          array (
            'dataGrid_action_delete' => 
            array (
              'id' => 'dataGrid_action_delete',
              'parent' => 0,
              'name' => 'dataGrid_action_delete',
              'class' => 'Ext_Grid_Column_Action_Button',
              'extClass' => 'Grid_Column_Action_Button',
              'order' => false,
              'state' => 
              array (
                'config' => 
                array (
                  'icon' => '[%wroot%]i/system/delete.gif',
                  'text' => 'dataGrid_action_delete',
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