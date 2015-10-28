<?php return array (
  'table' => 'menu_items',
  'engine' => 'InnoDB',
  'rev_control' => false,
  'save_history' => true,
  'link_title' => 'title',
  'system' => true,
  'fields' => 
  array (
    'page_id' => 
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
    'menu_id' => 
    array (
      'type' => 'link',
      'unique' => 'menu_tree',
      'db_isNull' => false,
      'required' => true,
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
    'published' =>
    array (
        'type' => '',
        'unique' => '',
        'db_isNull' => false,
        'required' => false,
        'db_type' => 'boolean',
        'db_default' => 0,
    ),
    'order' => 
    array (
      'type' => '',
      'unique' => '',
      'db_isNull' => false,
      'required' => false,
      'db_type' => 'int',
      'db_default' => 0,
      'db_unsigned' => true,
    ),
    'parent_id' => 
    array (
      'type' => '',
      'unique' => '',
      'db_isNull' => false,
      'required' => true,
      'db_type' => 'int',
      'db_default' => 0,
      'db_unsigned' => true,
    ),
    'tree_id' => 
    array (
      'type' => '',
      'unique' => 'menu_tree',
      'db_isNull' => false,
      'required' => true,
      'db_type' => 'int',
      'db_default' => 0,
      'db_unsigned' => true,
    ),
    'link_type' => 
    array (
      'type' => 'link',
      'unique' => '',
      'required' => true,
      'link_config' => 
      array (
        'link_type' => 'dictionary',
        'object' => 'link_type',
      ),
      'db_type' => 'varchar',
      'db_len' => 255,
      'db_isNull' => false,
      'db_default' => false,
    ),
    'url' => 
    array (
      'type' => '',
      'unique' => '',
      'db_isNull' => false,
      'required' => false,
      'db_type' => 'tinytext',
      'db_default' => '',
      'is_search' => false,
      'allow_html' => false,
    ),
    'resource_id' => 
    array (
      'type' => 'link',
      'unique' => '',
      'db_isNull' => true,
      'required' => false,
      'validator' => '',
      'link_config' => 
      array (
        'link_type' => 'object',
        'object' => 'medialib',
      ),
      'db_type' => 'bigint',
      'db_default' => false,
      'db_unsigned' => true,
    ),
  ),
  'indexes' => 
  array (
    'menu_tree' => 
    array (
      'columns' => 
      array (
        0 => 'menu_id',
        1 => 'tree_id',
      ),
      'unique' => true,
      'fulltext' => false,
      'PRIMARY' => false,
    ),
  ),
  'connection' => 'default',
  'locked' => false,
  'readonly' => false,
  'primary_key' => 'id',
  'use_db_prefix' => true,
  'acl' => false,
  'slave_connection' => 'default',
  'disable_keys' => false,
); 