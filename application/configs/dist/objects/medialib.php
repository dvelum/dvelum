<?php return array (
  'table' => 'medialib',
  'engine' => 'InnoDB',
  'rev_control' => false,
  'link_title' => 'title',
  'save_history' => true,
  'system' => true,
  'fields' => 
  array (
    'title' => 
    array (
      'type' => '',
      'unique' => '',
      'db_isNull' => false,
      'required' => true,
      'validator' => '',
      'db_type' => 'varchar',
      'db_default' => '',
      'db_len' => 255,
      'is_search' => true,
      'allow_html' => false,
    ),
    'date' => 
    array (
      'type' => '',
      'unique' => '',
      'db_isNull' => false,
      'required' => true,
      'validator' => '',
      'db_type' => 'datetime',
    ),
    'alttext' => 
    array (
      'type' => '',
      'unique' => '',
      'db_isNull' => false,
      'required' => false,
      'validator' => '',
      'db_type' => 'varchar',
      'db_default' => '',
      'db_len' => 255,
      'is_search' => false,
      'allow_html' => false,
    ),
    'caption' => 
    array (
      'type' => '',
      'unique' => '',
      'db_isNull' => true,
      'required' => false,
      'validator' => '',
      'db_type' => 'text',
      'db_default' => false,
      'is_search' => true,
      'allow_html' => false,
    ),
    'description' => 
    array (
      'type' => '',
      'unique' => '',
      'db_isNull' => true,
      'required' => false,
      'validator' => '',
      'db_type' => 'longtext',
      'db_default' => false,
      'is_search' => false,
      'allow_html' => false,
    ),
    'size' => 
    array (
      'type' => '',
      'unique' => '',
      'db_isNull' => false,
      'required' => true,
      'validator' => '',
      'db_type' => 'decimal',
      'db_default' => 0,
      'db_unsigned' => true,
      'db_scale' => 10,
      'db_precision' => 4,
    ),
    'user_id' => 
    array (
      'type' => 'link',
      'unique' => '',
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
    'path' => 
    array (
      'type' => '',
      'unique' => 'path',
      'db_isNull' => false,
      'required' => true,
      'validator' => '',
      'db_type' => 'varchar',
      'db_default' => '',
      'db_len' => 255,
      'is_search' => false,
      'allow_html' => false,
    ),
    'type' => 
    array (
      'type' => '',
      'unique' => '',
      'db_isNull' => false,
      'required' => true,
      'validator' => '',
      'db_type' => 'varchar',
      'db_default' => '',
      'db_len' => 50,
      'is_search' => false,
      'allow_html' => false,
    ),
    'ext' => 
    array (
      'db_type' => 'varchar',
      'db_len' => 10,
      'db_isNull' => false,
      'required' => true,
    ),
    'modified' => 
    array (
      'type' => '',
      'unique' => '',
      'db_isNull' => true,
      'required' => false,
      'validator' => '',
      'db_type' => 'datetime',
    ),
    'croped' => 
    array (
      'type' => '',
      'unique' => '',
      'db_isNull' => false,
      'required' => false,
      'validator' => '',
      'db_type' => 'boolean',
      'db_default' => 0,
    ),
    'category' => 
    array (
      'type' => 'link',
      'unique' => '',
      'db_isNull' => true,
      'required' => false,
      'validator' => '',
      'link_config' => 
      array (
        'link_type' => 'object',
        'object' => 'mediacategory',
      ),
      'db_type' => 'bigint',
      'db_default' => false,
      'db_unsigned' => true,
    ),
  ),
  'indexes' => 
  array (
    'path' => 
    array (
      'columns' => 
      array (
        0 => 'path',
      ),
      'unique' => true,
      'fulltext' => false,
      'PRIMARY' => false,
    ),
    'title' => 
    array (
      'fulltext' => false,
      'unique' => false,
      'columns' => 
      array (
        0 => 'title',
      ),
    ),
  ),
  'connection' => 'default',
  'locked' => false,
  'readonly' => false,
  'primary_key' => 'id',
  'use_db_prefix' => true,
  'disable_keys' => false,
); 