<?php return array (
  'table' => 'user_settings',
  'engine' => 'InnoDB',
  'connection' => 'default',
  'acl' => false,
  'data_object' => '',
  'parent_object' => '',
  'rev_control' => false,
  'save_history' => false,
  'link_title' => 'user',
  'disable_keys' => false,
  'readonly' => false,
  'locked' => false,
  'primary_key' => 'id',
  'use_db_prefix' => true,
  'log_detalization' => 'default',
  'distributed' => false,
  'sharding_type' => NULL,
  'sharding_key' => NULL,
  'system' => true,
  'fields' => 
  array (
    'language' => 
    array (
      'type' => '',
      'unique' => '',
      'db_isNull' => true,
      'required' => false,
      'validator' => '',
      'db_type' => 'varchar',
      'db_default' => false,
      'db_len' => 3,
      'is_search' => false,
      'allow_html' => false,
    ),
    'theme' => 
    array (
      'type' => '',
      'unique' => '',
      'db_isNull' => true,
      'required' => false,
      'validator' => '',
      'db_type' => 'varchar',
      'db_default' => false,
      'db_len' => 255,
      'is_search' => false,
      'allow_html' => false,
    ),
    'user' => 
    array (
      'type' => 'link',
      'unique' => 'user',
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
  ),
  'indexes' => 
  array (
    'user' => 
    array (
      'columns' => 
      array (
        0 => 'user',
      ),
      'unique' => true,
      'fulltext' => false,
      'PRIMARY' => false,
    ),
  ),
); 