<?php return array (
  'table' => 'bgtask_signal',
  'engine' => 'Memory',
  'rev_control' => false,
  'save_history' => false,
  'link_title' => '',
  'system' => true,
  'fields' => 
  array (
    'pid' => 
    array (
      'type' => 'link',
      'unique' => '',
      'required' => true,
      'link_config' => 
      array (
        'link_type' => 'object',
        'object' => 'bgtask',
      ),
      'db_type' => 'bigint',
      'db_isNull' => false,
      'db_default' => false,
      'db_unsigned' => true,
    ),
    'signal' => 
    array (
      'type' => '',
      'unique' => '',
      'db_isNull' => false,
      'required' => true,
      'db_type' => 'bigint',
      'db_default' => 0,
      'db_unsigned' => true,
    ),
  ),
  'indexes' => 
  array (
  ),
  'connection' => 'default',
  'locked' => false,
  'readonly' => false,
  'primary_key' => 'id',
  'use_db_prefix' => true,
); 