<?php return array (
  'table' => 'user',
  'engine' => 'InnoDB',
  'rev_control' => false,
  'link_title' => 'name',
  'save_history' => true,
  'system' => true,
  'fields' => 
  array (
    'name' => 
    array (
      'required' => true,
      'db_type' => 'varchar',
      'db_len' => 255,
      'db_isNull' => 0,
      'db_default' => '',
      'is_search' => true,
    ),
    'email' => 
    array (
      'type' => '',
      'unique' => 'email',
      'db_isNull' => true,
      'required' => false,
      'validator' => '\\Dvelum\\Validator\\Email',
      'db_type' => 'varchar',
      'db_default' => '',
      'db_len' => 255,
      'is_search' => true,
      'allow_html' => false,
    ),
    'login' => 
    array (
      'type' => '',
      'unique' => 'login',
      'db_isNull' => false,
      'required' => true,
      'validator' => '\\Dvelum\\Validator\\Login',
      'db_type' => 'varchar',
      'db_default' => '',
      'db_len' => 255,
      'is_search' => true,
      'allow_html' => false,
    ),
    'pass' => 
    array (
      'type' => '',
      'unique' => '',
      'db_isNull' => false,
      'required' => false,
      'db_type' => 'varchar',
      'db_default' => '',
      'db_len' => 255,
      'is_search' => false,
      'allow_html' => false,
    ),
    'enabled' => 
    array (
      'db_type' => 'boolean',
      'db_isNull' => false,
      'db_default' => 0,
    ),
    'admin' => 
    array (
      'db_type' => 'boolean',
      'db_isNull' => false,
      'db_default' => 0,
    ),
    'registration_date' => 
    array (
      'type' => '',
      'unique' => '',
      'db_isNull' => true,
      'required' => false,
      'validator' => '',
      'db_type' => 'datetime',
    ),
    'confirmation_code' => 
    array (
      'db_type' => 'char',
      'db_len' => 32,
      'db_isNull' => true,
      'db_default' => '',
    ),
    'group_id' => 
    array (
      'type' => 'link',
      'unique' => '',
      'db_isNull' => true,
      'required' => false,
      'validator' => '',
      'link_config' => 
      array (
        'link_type' => 'object',
        'object' => 'group',
      ),
      'db_type' => 'bigint',
      'db_default' => false,
      'db_unsigned' => true,
    ),
    'confirmed' => 
    array (
      'db_type' => 'boolean',
      'db_isNull' => false,
      'db_default' => 0,
    ),
    'avatar' => 
    array (
      'type' => '',
      'unique' => '',
      'db_isNull' => false,
      'required' => false,
      'db_type' => 'varchar',
      'db_default' => '',
      'db_len' => 255,
      'is_search' => false,
      'allow_html' => false,
    ),
    'registration_ip' => 
    array (
      'type' => '',
      'unique' => '',
      'db_isNull' => false,
      'required' => false,
      'db_type' => 'varchar',
      'db_default' => '',
      'db_len' => 39,
      'is_search' => false,
      'allow_html' => false,
    ),
    'last_ip' => 
    array (
      'type' => '',
      'unique' => '',
      'db_isNull' => false,
      'required' => false,
      'db_type' => 'varchar',
      'db_default' => '',
      'db_len' => 39,
      'is_search' => false,
      'allow_html' => false,
    ),
    'confirmation_date' => 
    array (
      'type' => '',
      'unique' => '',
      'db_isNull' => true,
      'required' => false,
      'db_type' => 'datetime',
    ),
    'confirmation_expiried' => 
    array (
      'type' => '',
      'unique' => '',
      'db_isNull' => true,
      'required' => false,
      'validator' => '',
      'db_type' => 'datetime',
      'db_default' => false,
    ),
  ),
  'indexes' => 
  array (
    'login' => 
    array (
      'columns' => 
      array (
        0 => 'login',
      ),
      'fulltext' => false,
      'unique' => true,
    ),
    'email' => 
    array (
      'columns' => 
      array (
        0 => 'email',
      ),
      'fulltext' => false,
      'unique' => false,
    ),
  ),
  'disable_keys' => false,
  'connection' => 'default',
  'locked' => false,
  'readonly' => false,
  'primary_key' => 'id',
  'use_db_prefix' => true,
  'acl' => false,
  'distributed' => false,
); 