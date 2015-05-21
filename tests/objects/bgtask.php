<?php return array (
  'table' => 'bgtask',
  'engine' => 'Memory',
  'rev_control' => false,
  'save_history' => false,
  'link_title' => 'title',
  'system' => true,
  'fields' => 
  array (
    'status' => 
    array (
      'type' => 'link',
      'unique' => '',
      'required' => false,
      'link_config' => 
      array (
        'link_type' => 'dictionary',
        'object' => 'task',
      ),
      'db_type' => 'varchar',
      'db_len' => 255,
      'db_isNull' => false,
      'db_default' => '',
    ),
    'title' => 
    array (
      'type' => '',
      'unique' => '',
      'db_isNull' => false,
      'required' => true,
      'db_type' => 'varchar',
      'db_default' => '',
      'db_len' => 255,
      'is_search' => false,
      'allow_html' => false,
    ),
    'parent' => 
    array (
      'type' => 'link',
      'unique' => '',
      'required' => false,
      'link_config' => 
      array (
        'link_type' => 'object',
        'object' => 'bgtask',
      ),
      'db_type' => 'bigint',
      'db_isNull' => true,
      'db_default' => false,
      'db_unsigned' => true,
    ),
    'op_total' => 
    array (
      'type' => '',
      'unique' => '',
      'db_isNull' => false,
      'required' => false,
      'validator' => '',
      'db_type' => 'bigint',
      'db_default' => 0,
      'db_unsigned' => true,
    ),
    'op_finished' => 
    array (
      'type' => '',
      'unique' => '',
      'db_isNull' => false,
      'required' => false,
      'validator' => '',
      'db_type' => 'bigint',
      'db_default' => 0,
      'db_unsigned' => true,
    ),
    'memory' => 
    array (
      'type' => '',
      'unique' => '',
      'db_isNull' => false,
      'required' => false,
      'validator' => '',
      'db_type' => 'bigint',
      'db_default' => 0,
      'db_unsigned' => true,
    ),
    'time_started' => 
    array (
      'type' => '',
      'unique' => '',
      'db_isNull' => true,
      'required' => false,
      'validator' => '',
      'db_type' => 'datetime',
    ),
    'time_finished' => 
    array (
      'type' => '',
      'unique' => '',
      'db_isNull' => true,
      'required' => false,
      'validator' => '',
      'db_type' => 'datetime',
    ),
    'memory_peak' => 
    array (
      'type' => '',
      'unique' => '',
      'db_isNull' => false,
      'required' => false,
      'validator' => '',
      'db_type' => 'bigint',
      'db_default' => 0,
      'db_unsigned' => true,
    ),
  ),
  'indexes' => 
  array (
  ),
  'connection' => 'default',
  'locked' => false,
  'readonly' => false,
  'primary_key' => 'id',
  'use_db_prefix' => true,
); 