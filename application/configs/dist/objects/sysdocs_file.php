<?php return array (
  'table' => 'sysdocs_file',
  'engine' => 'InnoDB',
  'connection' => 'default',
  'acl' => false,
  'rev_control' => false,
  'save_history' => false,
  'link_title' => 'name',
  'disable_keys' => false,
  'readonly' => false,
  'locked' => false,
  'primary_key' => 'id',
  'use_db_prefix' => false,
  'system' => true,
  'fields' => 
  array (
    'path' => 
    array (
      'type' => '',
      'unique' => '',
      'db_isNull' => false,
      'required' => true,
      'validator' => '',
      'db_type' => 'varchar',
      'db_default' => false,
      'db_len' => 255,
      'is_search' => true,
      'allow_html' => false,
    ),
    'isDir' => 
    array (
      'type' => '',
      'unique' => '',
      'db_isNull' => false,
      'required' => false,
      'validator' => '',
      'db_type' => 'boolean',
      'db_default' => false,
    ),
    'name' => 
    array (
      'type' => '',
      'unique' => '',
      'db_isNull' => false,
      'required' => true,
      'validator' => '',
      'db_type' => 'varchar',
      'db_default' => false,
      'db_len' => 255,
      'is_search' => false,
      'allow_html' => false,
    ),
    'vers' => 
    array (
      'type' => '',
      'unique' => 'hid_vers',
      'db_isNull' => false,
      'required' => true,
      'validator' => '',
      'db_type' => 'int',
      'db_default' => false,
      'db_unsigned' => true,
    ),
    'hid' => 
    array (
      'type' => '',
      'unique' => 'hid_vers',
      'db_isNull' => false,
      'required' => true,
      'validator' => '',
      'db_type' => 'char',
      'db_default' => false,
      'db_len' => 32,
      'is_search' => false,
      'allow_html' => false,
    ),
    'parentId' => 
    array (
      'type' => 'link',
      'unique' => '',
      'db_isNull' => true,
      'required' => false,
      'validator' => '',
      'link_config' => 
      array (
        'link_type' => 'object',
        'object' => 'sysdocs_file',
      ),
      'db_type' => 'bigint',
      'db_default' => false,
      'db_unsigned' => true,
    ),
  ),
  'indexes' => 
  array (
    'vers' => 
    array (
      'columns' => 
      array (
        0 => 'vers',
      ),
      'unique' => false,
      'fulltext' => false,
      'PRIMARY' => false,
    ),
    'hid_vers' => 
    array (
      'columns' => 
      array (
        0 => 'hid',
        1 => 'vers',
      ),
      'unique' => true,
      'fulltext' => false,
      'PRIMARY' => false,
    ),
  ),
  'slave_connection' => 'default',
); 