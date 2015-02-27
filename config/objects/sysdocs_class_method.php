<?php return array (
  'table' => 'sysdocs_class_method',
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
    'classId' => 
    array (
      'type' => 'link',
      'unique' => '',
      'db_isNull' => false,
      'required' => true,
      'validator' => '',
      'link_config' => 
      array (
        'link_type' => 'object',
        'object' => 'sysdocs_class',
      ),
      'db_type' => 'bigint',
      'db_default' => false,
      'db_unsigned' => true,
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
      'is_search' => true,
      'allow_html' => false,
    ),
    'deprecated' => 
    array (
      'type' => '',
      'unique' => '',
      'db_isNull' => false,
      'required' => false,
      'validator' => '',
      'db_type' => 'boolean',
      'db_default' => 0,
    ),
    'description' => 
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
    'throws' => 
    array (
      'type' => '',
      'unique' => '',
      'db_isNull' => true,
      'required' => false,
      'validator' => '',
      'db_type' => 'varchar',
      'db_default' => false,
      'db_len' => 255,
      'is_search' => false,
      'allow_html' => false,
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
    'abstract' => 
    array (
      'type' => '',
      'unique' => '',
      'db_isNull' => false,
      'required' => false,
      'validator' => '',
      'db_type' => 'boolean',
      'db_default' => 0,
    ),
    'static' => 
    array (
      'type' => '',
      'unique' => '',
      'db_isNull' => false,
      'required' => false,
      'validator' => '',
      'db_type' => 'boolean',
      'db_default' => 0,
    ),
    'visibility' => 
    array (
      'type' => 'link',
      'unique' => '',
      'db_isNull' => false,
      'required' => false,
      'validator' => '',
      'link_config' => 
      array (
        'link_type' => 'dictionary',
        'object' => 'sysdocs_visibility',
      ),
      'db_type' => 'varchar',
      'db_len' => 255,
      'db_default' => '',
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
    'returnType' => 
    array (
      'type' => '',
      'unique' => '',
      'db_isNull' => true,
      'required' => false,
      'validator' => '',
      'db_type' => 'varchar',
      'db_default' => false,
      'db_len' => 255,
      'is_search' => false,
      'allow_html' => false,
    ),
    'classHid' => 
    array (
      'type' => '',
      'unique' => '',
      'db_isNull' => false,
      'required' => true,
      'validator' => '',
      'db_type' => 'char',
      'db_default' => false,
      'db_len' => 32,
      'is_search' => false,
      'allow_html' => false,
    ),
    'final' => 
    array (
      'type' => '',
      'unique' => '',
      'db_isNull' => false,
      'required' => false,
      'validator' => '',
      'db_type' => 'boolean',
      'db_default' => 0,
    ),
    'inherited' => 
    array (
      'type' => '',
      'unique' => '',
      'db_isNull' => false,
      'required' => false,
      'validator' => '',
      'db_type' => 'boolean',
      'db_default' => 0,
    ),
    'returnsReference' => 
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
  ),
); 