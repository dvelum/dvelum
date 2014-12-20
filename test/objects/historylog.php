<?php return array (
  'table' => 'historylog',
  'engine' => 'InnoDB',
  'rev_control' => false,
  'link_title' => 'type',
  'save_history' => false,
  'system' => true,
  'disable_keys' => true,
  'fields' => 
  array (
    'user_id' => 
    array (
      'type' => 'link',
      'unique' => '',
      'db_isNull' => false,
      'required' => true,
      'validator' => '',
      'link_config' => 
      array (
        'link_type' => 'object',
        'object' => 'user',
      ),
      'db_type' => 'bigint',
      'db_default' => false,
      'db_unsigned' => true,
    ),
    'date' => 
    array (
      'db_len' => false,
      'db_type' => 'datetime',
      'db_isNull' => 1,
      'required' => false,
      'db_default' => NULL,
    ),
    'record_id' => 
    array (
      'required' => true,
      'db_type' => 'bigint',
      'db_len' => 11,
      'db_isNull' => 0,
      'db_default' => 0,
      'db_unsigned' => true,
    ),
    'type' => 
    array (
      'required' => true,
      'db_type' => 'tinyint',
      'db_len' => 4,
      'db_isNull' => 0,
      'db_default' => 0,
      'db_unsigned' => true,
    ),
    'table_name' => 
    array (
      'required' => true,
      'db_type' => 'varchar',
      'db_len' => 100,
      'db_isNull' => 0,
      'db_default' => '',
    ),
  ),
  'indexes' => 
  array (
  ),
); 