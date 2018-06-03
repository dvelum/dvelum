<?php return array (
  'table' => 'acl_simple',
  'engine' => 'InnoDB',
  'connection' => 'default',
  'rev_control' => false,
  'save_history' => true,
  'link_title' => 'object',
  'disable_keys' => false,
  'readonly' => false,
  'locked' => false,
  'primary_key' => 'id',
  'use_db_prefix' => true,
  'system' => true,
  'fields' => 
  array (
    'user_id' => 
    array (
      'required' => false,
      'type' => 'link',
      'link_config' => 
      array (
        'link_type' => 'object',
        'object' => 'user',
      ),
      'unique' => 'user_group_object',
      'db_type' => 'bigint',
      'db_isNull' => true,
      'db_unsigned' => true,
      'db_default' => false,
    ),
    'group_id' => 
    array (
      'type' => 'link',
      'unique' => 'user_group_object',
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
    'view' => 
    array (
      'required' => false,
      'db_type' => 'boolean',
      'db_len' => 1,
      'db_isNull' => 0,
      'db_default' => 0,
      'db_unsigned' => true,
    ),
    'create' => 
    array (
      'required' => false,
      'db_type' => 'boolean',
      'db_len' => 1,
      'db_isNull' => 0,
      'db_default' => 0,
      'db_unsigned' => true,
    ),
    'edit' => 
    array (
      'required' => false,
      'db_type' => 'boolean',
      'db_len' => 1,
      'db_isNull' => 0,
      'db_default' => 0,
      'db_unsigned' => true,
    ),
    'delete' => 
    array (
      'type' => '',
      'unique' => '',
      'db_isNull' => false,
      'required' => false,
      'validator' => '',
      'db_type' => 'boolean',
      'db_default' => 0,
    ),
    'object' => 
    array (
      'required' => true,
      'db_type' => 'varchar',
      'db_len' => 255,
      'db_isNull' => 0,
      'db_default' => '',
      'unique' => 'user_group_object',
    ),
    'publish' => 
    array (
      'type' => '',
      'unique' => '',
      'db_isNull' => false,
      'required' => false,
      'validator' => '',
      'db_type' => 'boolean',
      'db_default' => 0,
    ),
  ),
  'indexes' => 
  array (
    'user_object' => 
    array (
      'columns' => 
      array (
        0 => 'user_id',
        1 => 'object',
      ),
      'unique' => true,
      'fulltext' => false,
      'PRIMARY' => false,
    ),
    'group_object' => 
    array (
      'columns' => 
      array (
        0 => 'object',
        1 => 'group_id',
      ),
      'unique' => true,
      'fulltext' => false,
      'PRIMARY' => false,
    ),
  ),
  'acl' => false,
  'slave_connection' => 'default',
); 