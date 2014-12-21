<?php return array (
  'table' => 'user_groups',
  'engine' => 'InnoDB',
  'rev_control' => false,
  'link_title' => 'title',
  'save_history' => false,
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
    'system' => 
    array (
      'db_type' => 'boolean',
      'db_default' => 0,
      'db_isNull' => false,
      'required' => false,
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