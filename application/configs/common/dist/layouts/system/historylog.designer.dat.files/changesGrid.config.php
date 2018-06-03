<?php return array (
  'id' => 'changesGrid',
  'class' => 'Ext_Grid',
  'extClass' => 'Grid',
  'name' => 'changesGrid',
  'state' => 
  array (
    'config' => 
    array (
      'store' => 'changesStore',
      'columnLines' => true,
      'viewConfig' => '{enableTextSelection:true}',
      'title' => '[js:] appLang.CHANGES',
      'flex' => 1.0,
      'isExtended' => false,
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
      'field' => 
      array (
        'id' => 'field',
        'parent' => 0,
        'class' => 'Ext_Grid_Column',
        'name' => 'field',
        'extClass' => 'Grid_Column',
        'order' => 0,
        'state' => 
        array (
          'config' => 
          array (
            'dataIndex' => 'id',
            'text' => '[js:] appLang.FIELD',
            'width' => 157.0,
          ),
        ),
      ),
      'before' => 
      array (
        'id' => 'before',
        'parent' => 0,
        'class' => 'Ext_Grid_Column',
        'name' => 'before',
        'extClass' => 'Grid_Column',
        'order' => 1,
        'state' => 
        array (
          'config' => 
          array (
            'dataIndex' => 'before',
            'text' => '[js:] appLang.BEFORE',
            'width' => 354.0,
          ),
        ),
      ),
      'after' => 
      array (
        'id' => 'after',
        'parent' => 0,
        'class' => 'Ext_Grid_Column',
        'name' => 'after',
        'extClass' => 'Grid_Column',
        'order' => 2,
        'state' => 
        array (
          'config' => 
          array (
            'dataIndex' => 'after',
            'text' => '[js:] appLang.AFTER',
            'width' => 369.0,
          ),
        ),
      ),
    ),
  ),
); 