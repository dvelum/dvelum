<?php return array (
  'id' => 'dateFilter',
  'class' => 'Ext_Component_Filter',
  'extClass' => 'Component_Filter',
  'name' => 'dateFilter',
  'state' => 
  array (
    'config' => 
    array (
      'store' => 
      array (
        'class' => 'Ext_Helper_Store',
        'state' => 
        array (
          'type' => 'jscall',
          'value' => 'me.getStore()',
        ),
      ),
      'storeField' => 'date',
      'autoFilter' => true,
      'isExtended' => false,
    ),
    'viewObject' => 
    array (
      'class' => 'Form_Field_Date',
      'state' => 
      array (
        'config' => 
        array (
          'format' => 'd.m.Y',
          'submitFormat' => 'Y-m-d',
        ),
      ),
    ),
  ),
); 