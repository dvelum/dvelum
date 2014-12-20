<?php return array (
  'table' => 'content',
  'engine' => 'InnoDB',
  'rev_control' => true,
  'link_title' => 'menu_title',
  'save_history' => true,
  'system' => true,
  'fields' => 
  array (
    'is_fixed' => 
    array (
      'required' => false,
      'db_type' => 'boolean',
      'db_isNull' => 0,
      'db_default' => 0,
    ),
    'parent_id' => 
    array (
      'type' => 'link',
      'unique' => '',
      'db_isNull' => true,
      'required' => false,
      'validator' => '',
      'link_config' => 
      array (
        'link_type' => 'object',
        'object' => 'page',
      ),
      'db_type' => 'bigint',
      'db_default' => false,
      'db_unsigned' => true,
    ),
    'code' => 
    array (
      'type' => '',
      'unique' => 'code',
      'db_isNull' => false,
      'required' => true,
      'validator' => '',
      'db_type' => 'varchar',
      'db_default' => false,
      'db_len' => 255,
      'is_search' => true,
      'allow_html' => false,
    ),
    'page_title' => 
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
    'menu_title' => 
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
    'html_title' => 
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
    'meta_keywords' => 
    array (
      'type' => '',
      'unique' => '',
      'db_isNull' => true,
      'required' => false,
      'validator' => '',
      'db_type' => 'text',
      'db_default' => false,
      'is_search' => false,
      'allow_html' => false,
    ),
    'meta_description' => 
    array (
      'type' => '',
      'unique' => '',
      'db_isNull' => true,
      'required' => false,
      'validator' => '',
      'db_type' => 'text',
      'db_default' => false,
      'is_search' => false,
      'allow_html' => false,
    ),
    'text' => 
    array (
      'allow_html' => true,
      'db_type' => 'longtext',
      'db_isNull' => 0,
      'db_default' => '',
    ),
    'func_code' => 
    array (
      'required' => false,
      'db_type' => 'varchar',
      'db_len' => 255,
      'db_isNull' => 0,
      'db_default' => '',
    ),
    'show_blocks' => 
    array (
      'required' => false,
      'db_type' => 'boolean',
      'db_len' => 1,
      'db_isNull' => 0,
      'db_default' => 0,
    ),
    'in_site_map' => 
    array (
      'db_type' => 'boolean',
      'db_len' => 1,
      'db_isNull' => 0,
      'db_default' => 0,
    ),
    'order_no' => 
    array (
      'db_type' => 'smallint',
      'db_len' => 4,
      'db_isNull' => 0,
      'db_default' => 0,
      'db_unsigned' => true,
    ),
    'blocks' => 
    array (
      'type' => '',
      'unique' => false,
      'db_isNull' => true,
      'required' => false,
      'db_type' => 'longtext',
      'db_default' => '',
      'is_search' => false,
      'allow_html' => true,
    ),
    'theme' => 
    array (
      'type' => '',
      'unique' => false,
      'db_isNull' => false,
      'required' => false,
      'db_type' => 'varchar',
      'db_default' => 'default',
      'db_len' => 100,
      'is_search' => false,
      'allow_html' => false,
    ),
    'default_blocks' => 
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
    'code' => 
    array (
      'columns' => 
      array (
        0 => 'code',
      ),
      'fulltext' => false,
      'unique' => true,
    ),
    'code_published' => 
    array (
      'columns' => 
      array (
        0 => 'code',
        1 => 'published',
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
); 