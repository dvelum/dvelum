<?php return array (
  'table' => 'test_sharding',
  'engine' => 'InnoDB',
  'connection' => 'default',
  'acl' => false,
  'data_object' => '',
  'parent_object' => '',
  'rev_control' => false,
  'save_history' => false,
  'link_title' => '',
  'disable_keys' => false,
  'readonly' => false,
  'locked' => false,
  'primary_key' => 'id',
  'use_db_prefix' => true,
  'log_detalization' => 'default',
  'distributed' => true,
  'sharding_type' => 'sharding_key',
  'sharding_key' => 'code',
  'fields' => 
  array (
    'code' => 
    array (
      'type' => '',
      'unique' => '',
      'db_isNull' => false,
      'required' => true,
      'validator' => '',
      'db_type' => 'varchar',
      'db_default' => false,
      'db_len' => 255,
      'is_search' => false,
      'allow_html' => false,
      'name' => 'code',
    ),
    'title' => 
    array (
      'type' => '',
      'unique' => '',
      'db_isNull' => false,
      'required' => true,
      'validator' => '',
      'db_type' => 'varchar',
      'db_default' => false,
      'db_len' => 255,
      'is_search' => false,
      'allow_html' => false,
    ),
  ),
  'indexes' => 
  array (
  ),
  'distributed_indexes' => 
  array (
  ),
); 