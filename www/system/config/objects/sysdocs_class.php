<?php return array (
  'table' => 'sysdocs_class',
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
    'itemType' => 
    array (
      'type' => 'link',
      'unique' => '',
      'db_isNull' => false,
      'required' => true,
      'validator' => '',
      'link_config' => 
      array (
        'link_type' => 'dictionary',
        'object' => 'sysdocs_item_type',
      ),
      'db_type' => 'varchar',
      'db_len' => 255,
      'db_default' => false,
    ),
    'fileId' => 
    array (
      'type' => 'link',
      'unique' => 'file_version',
      'db_isNull' => false,
      'required' => true,
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
        'object' => 'sysdocs_class',
      ),
      'db_type' => 'bigint',
      'db_default' => false,
      'db_unsigned' => true,
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
    'namespace' => 
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
    'fileHid' => 
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
    'implements' => 
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
    'extends' => 
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
    'parentId' => 
    array (
      'columns' => 
      array (
        0 => 'parentId',
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
); 