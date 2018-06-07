<?php return array (
  'table' => 'blocks',
  'engine' => 'InnoDB',
  'rev_control' => true,
  'link_title' => 'title',
  'save_history' => true,
  'system' => true,
  'fields' => 
  array (
    'title' => 
    array (
      'db_type' => 'varchar',
      'db_len' => 255,
      'db_isNull' => false,
      'required' => true,
      'is_search' => true,
    ),
    'text' => 
    array (
      'type' => '',
      'unique' => '',
      'db_isNull' => false,
      'required' => false,
      'db_type' => 'longtext',
      'db_default' => '',
      'is_search' => false,
      'allow_html' => true,
    ),
    'show_title' => 
    array (
      'type' => '',
      'unique' => false,
      'db_isNull' => false,
      'required' => false,
      'db_type' => 'boolean',
      'db_default' => 0,
    ),
    'is_system' => 
    array (
      'db_type' => 'boolean',
      'db_isNull' => false,
      'db_default' => 0,
      'required' => false,
    ),
    'sys_name' => 
    array (
      'type' => '',
      'unique' => '',
      'db_isNull' => true,
      'required' => false,
      'validator' => '',
      'db_type' => 'varchar',
      'db_default' => '',
      'db_len' => 255,
      'is_search' => false,
      'allow_html' => false,
    ),
    'params' => 
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
    'is_menu' => 
    array (
      'type' => '',
      'unique' => '',
      'db_isNull' => false,
      'required' => false,
      'db_type' => 'boolean',
      'db_default' => 0,
    ),
    'menu_id' => 
    array (
      'type' => 'link',
      'unique' => '',
      'db_isNull' => true,
      'required' => false,
      'validator' => '',
      'link_config' => 
      array (
        'link_type' => 'object',
        'object' => 'menu',
      ),
      'db_type' => 'bigint',
      'db_default' => false,
      'db_unsigned' => true,
    ),
  ),
  'indexes' => 
  array (
  ),
  'connection' => 'default',
  'locked' => false,
  'readonly' => false,
  'disable_keys' => false,
  'primary_key' => 'id',
  'use_db_prefix' => true,
  'acl' => false,
  'slave_connection' => 'default',
); 