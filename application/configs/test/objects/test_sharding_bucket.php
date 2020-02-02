<?php return array (
  'table' => 'test_sharding_bucket',
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
  'sharding_type' => 'virtual_bucket',
  'sharding_key' => 'id',
  'fields' => 
  array (
    'value' => 
    array (
      'type' => '',
      'unique' => '',
      'db_isNull' => false,
      'required' => true,
      'validator' => '',
      'db_type' => 'bigint',
      'db_default' => false,
      'db_unsigned' => true,
    ),
  ),
  'indexes' => 
  array (
  ),
  'distributed_indexes' => 
  array (
  ),
); 