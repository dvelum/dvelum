<?php return array (
  'table' => 'test_sharding_item',
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
  'sharding_type' => 'sharding_key_no_index',
  'sharding_key' => 'test_sharding',
  'fields' => 
  array (
    'test_sharding' => 
    array (
      'type' => 'link',
      'unique' => '',
      'db_isNull' => false,
      'required' => true,
      'validator' => '',
      'link_config' => 
      array (
        'link_type' => 'object',
        'object' => 'test_sharding',
      ),
      'db_type' => 'bigint',
      'db_default' => false,
      'db_unsigned' => true,
    ),
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