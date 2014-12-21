<?php return array (
  'table' => 'blockmapping',
  'engine' => 'InnoDB',
  'rev_control' => false,
  'save_history' => false,
  'link_title' => '',
  'system' => true,
  'fields' => 
  array (
    'page_id' => 
    array (
      'type' => 'link',
      'unique' => '',
      'db_isNull' => true,
      'required' => false,
      'validator' => '',
      'link_config' => 
      array (
        'link_type' => 'object',
        'object' => 'page',
      ),
      'db_type' => 'bigint',
      'db_default' => false,
      'db_unsigned' => true,
    ),
    'place' => 
    array (
      'type' => '',
      'unique' => '',
      'db_isNull' => false,
      'required' => true,
      'db_type' => 'varchar',
      'db_default' => '',
      'db_len' => 100,
      'is_search' => false,
      'allow_html' => false,
    ),
    'block_id' => 
    array (
      'type' => 'link',
      'unique' => '',
      'required' => true,
      'link_config' => 
      array (
        'link_type' => 'object',
        'object' => 'blocks',
      ),
      'db_type' => 'bigint',
      'db_isNull' => false,
      'db_default' => false,
      'db_unsigned' => true,
    ),
    'order_no' => 
    array (
      'type' => '',
      'unique' => '',
      'db_isNull' => false,
      'required' => false,
      'db_type' => 'int',
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