<?php return array (
  'data_object' => 'test_sharding_item',
  'connection' => 'sharding_index',
  'use_db_prefix' => true,
  'disable_keys' => true,
  'locked' => false,
  'readonly' => false,
  'primary_key' => 'id',
  'table' => 'test_sharding_item_dist_index',
  'engine' => 'InnoDB',
  'rev_control' => false,
  'link_title' => 'id',
  'save_history' => false,
  'system' => true,
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
      'name' => 'test_sharding',
    ),
  ),
  'indexes' => 
  array (
  ),
  'distributed' => false
); 