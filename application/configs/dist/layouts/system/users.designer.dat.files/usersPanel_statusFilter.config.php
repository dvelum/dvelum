<?php return array (
  'id' => 'usersPanel_statusFilter',
  'class' => 'Ext_Component_Filter',
  'extClass' => 'Component_Filter',
  'name' => 'usersPanel_statusFilter',
  'state' => 
  array (
    'config' => 
    array (
      'store' => 'usersStoreInstance',
      'storeField' => 'enabled',
      'local' => false,
      'autoFilter' => true,
    ),
    'viewObject' => 
    array (
      'class' => 'Form_Field_Combobox',
      'state' => 
      array (
        'config' => 
        array (
          'store' => '[new:] statusStore',
          'valueField' => 'id',
          'displayField' => 'title',
          'forceSelection' => false,
          'queryMode' => 'local',
          'typeAhead' => false,
          'emptyText' => '[js:] appLang.ALL',
          'triggers' => '{ clear: {cls: "x-form-clear-trigger", tooltip:appLang.RESET, defaultListenerScope: true,  handler:function(btn){btn.setValue(""); },  scope:this }  }',
        ),
      ),
    ),
  ),
); 