<?php return array (
  'data_object' => 'test_sharding',
  'connection' => 'sharding_index',
  'use_db_prefix' => true,
  'disable_keys' => true,
  'locked' => false,
  'readonly' => false,
  'primary_key' => 'id',
  'table' => 'test_sharding_dist_index',
  'engine' => 'InnoDB',
  'rev_control' => false,
  'link_title' => 'id',
  'save_history' => false,
  'system' => true,
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
  ),
  'indexes' => 
  array (
  ),
  'distributed' => false
); 