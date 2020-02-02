<?php return array (
  'table' => 'permissions',
  'engine' => 'InnoDB',
  'rev_control' => false,
  'link_title' => 'module',
  'save_history' => false,
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
      'unique' => 'user_group_module',
      'db_type' => 'bigint',
      'db_isNull' => true,
      'db_unsigned' => true,
      'db_default' => false,
    ),
    'group_id' => 
    array (
      'type' => 'link',
      'unique' => 'user_group_module',
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
    'publish' => 
    array (
      'required' => false,
      'db_type' => 'boolean',
      'db_len' => 1,
      'db_isNull' => 0,
      'db_default' => 0,
      'db_unsigned' => true,
    ),
    'module' => 
    array (
      'required' => true,
      'db_type' => 'varchar',
      'db_len' => 255,
      'db_isNull' => 0,
      'db_default' => '',
      'unique' => 'user_group_module',
    ),
    'only_own' =>
      array (
          'required' => false,
          'db_type' => 'boolean',
          'db_len' => 1,
          'db_isNull' => 0,
          'db_default' => 0,
          'db_unsigned' => true,
      ),
  ),
  'indexes' => 
  array (
    'user_group_module' => 
    array (
      'columns' => 
      array (
        0 => 'user_id',
        1 => 'group_id',
        2 => 'module',
      ),
      'fulltext' => false,
      'unique' => true,
    ),
    'user_id' => 
    array (
      'columns' => 
      array (
        0 => 'user_id',
      ),
      'fulltext' => false,
      'unique' => false,
    ),
    'group_id' => 
    array (
      'columns' => 
      array (
        0 => 'group_id',
      ),
      'fulltext' => false,
      'unique' => false,
    ),
  ),
  'connection' => 'default',
  'locked' => false,
  'readonly' => false,
  'primary_key' => 'id',
  'use_db_prefix' => true,
  'disable_keys' => false,
  'acl' => false
); 