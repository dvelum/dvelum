<?php return array (
  'table' => 'user_auth',
  'engine' => 'InnoDB',
  'connection' => 'default',
  'acl' => false,
  'rev_control' => false,
  'save_history' => false,
  'link_title' => 'user',
  'disable_keys' => false,
  'readonly' => false,
  'locked' => false,
  'primary_key' => 'id',
  'use_db_prefix' => true,
  'system' => true,
  'fields' => 
  array (
    'user' => 
    array (
      'type' => 'link',
      'unique' => 'user_auth',
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
    'type' => 
    array (
      'type' => 'link',
      'unique' => 'user_auth',
      'db_isNull' => false,
      'required' => true,
      'validator' => '',
      'link_config' => 
      array (
        'link_type' => 'dictionary',
        'object' => 'auth_type',
      ),
      'db_type' => 'varchar',
      'db_len' => 255,
      'db_default' => false,
    ),
    'config' => 
    array (
      'type' => 'encrypted',
      'unique' => '',
      'db_isNull' => false,
      'required' => true,
      'validator' => '',
      'db_default' => '{}',
      'db_type' => 'longtext',
      'is_search' => false,
      'allow_html' => true,
    ),
  ),
  'indexes' => 
  array (
  ),
); 