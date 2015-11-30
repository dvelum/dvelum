<?php return array (
  'table' => 'apikeys',
  'engine' => 'InnoDB',
  'rev_control' => false,
  'save_history' => true,
  'link_title' => 'name',
  'system' => true,
  'fields' => 
  array (
    'name' => 
    array (
      'type' => '',
      'unique' => 'name',
      'db_isNull' => false,
      'required' => true,
      'db_type' => 'varchar',
      'db_default' => '',
      'db_len' => 255,
      'is_search' => true,
      'allow_html' => false,
    ),
    'hash' => 
    array (
      'type' => '',
      'unique' => 'code',
      'db_isNull' => false,
      'required' => false,
      'db_type' => 'varchar',
      'db_default' => '',
      'db_len' => 100,
      'is_search' => false,
      'allow_html' => false,
    ),
    'active' => 
    array (
      'type' => '',
      'unique' => '',
      'db_isNull' => false,
      'required' => false,
      'db_type' => 'boolean',
      'db_default' => 0,
    ),
  ),
  'indexes' => 
  array (
    'name' => 
    array (
      'columns' => 
      array (
        0 => 'name',
      ),
      'unique' => true,
      'fulltext' => false,
      'PRIMARY' => false,
    ),
    'hash' => 
    array (
      'columns' => 
      array (
        0 => 'hash',
      ),
      'unique' => true,
      'fulltext' => false,
      'PRIMARY' => false,
    ),
    'ah' => 
    array (
      'columns' => 
      array (
        0 => 'active',
        1 => 'hash',
      ),
      'unique' => false,
      'fulltext' => false,
      'PRIMARY' => false,
    ),
  ),
  'disable_keys' => false,
  'connection' => 'default',
  'locked' => false,
  'readonly' => false,
  'primary_key' => 'id',
  'use_db_prefix' => true,
); 