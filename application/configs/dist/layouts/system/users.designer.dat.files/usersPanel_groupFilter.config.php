<?php return array (
  'id' => 'usersPanel_groupFilter',
  'class' => 'Ext_Component_Filter',
  'extClass' => 'Component_Filter',
  'name' => 'usersPanel_groupFilter',
  'state' => 
  array (
    'config' => 
    array (
      'store' => 'usersStoreInstance',
      'storeField' => 'group_id',
      'local' => false,
      'autoFilter' => true,
      'isExtended' => false,
    ),
    'viewObject' => 
    array (
      'class' => 'Form_Field_Combobox',
      'state' => 
      array (
        'config' => 
        array (
          'store' => '[new:] groupsStore',
          'valueField' => 'id',
          'displayField' => 'title',
          'forceSelection' => true,
          'allowBlank' => true,
          'emptyText' => '[js:] appLang.ALL',
          'triggers' => '{     clear: { 	cls: \'x-form-clear-trigger\', 	tooltip:appLang.RESET, 	handler:function(){ 	   me.childObjects.usersPanel_groupFilter.reset(); 	}, 	scope:me     } }',
        ),
      ),
    ),
  ),
); 