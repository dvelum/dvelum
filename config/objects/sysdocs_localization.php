<?php return array (
  'table' => 'sysdocs_localization',
  'engine' => 'InnoDB',
  'connection' => 'default',
  'acl' => false,
  'rev_control' => false,
  'save_history' => false,
  'link_title' => '',
  'disable_keys' => false,
  'readonly' => false,
  'locked' => false,
  'primary_key' => 'id',
  'use_db_prefix' => false,
  'system' => true,
  'fields' => 
  array (
    'lang' => 
    array (
      'type' => 'link',
      'unique' => '',
      'db_isNull' => false,
      'required' => true,
      'validator' => '',
      'link_config' => 
      array (
        'link_type' => 'dictionary',
        'object' => 'sysdocs_language',
      ),
      'db_type' => 'varchar',
      'db_len' => 255,
      'db_default' => false,
    ),
    'field' => 
    array (
      'type' => '',
      'unique' => '',
      'db_isNull' => false,
      'required' => true,
      'validator' => '',
      'db_type' => 'varchar',
      'db_default' => false,
      'db_len' => 50,
      'is_search' => true,
      'allow_html' => false,
    ),
    'object_id' => 
    array (
      'type' => '',
      'unique' => '',
      'db_isNull' => false,
      'required' => true,
      'validator' => '',
      'db_type' => 'bigint',
      'db_default' => false,
      'db_unsigned' => true,
    ),
    'value' => 
    array (
      'type' => '',
      'unique' => '',
      'db_isNull' => false,
      'required' => true,
      'validator' => '',
      'db_type' => 'text',
      'db_default' => false,
      'is_search' => true,
      'allow_html' => true,
    ),
    'vers' => 
    array (
      'type' => '',
      'unique' => '',
      'db_isNull' => false,
      'required' => true,
      'validator' => '',
      'db_type' => 'int',
      'db_default' => false,
      'db_unsigned' => true,
    ),
    'object_class' => 
    array (
      'type' => 'link',
      'unique' => '',
      'db_isNull' => false,
      'required' => true,
      'validator' => '',
      'link_config' => 
      array (
        'link_type' => 'dictionary',
        'object' => 'sysdocs_object',
      ),
      'db_type' => 'varchar',
      'db_len' => 255,
      'db_default' => false,
    ),
    'hid' => 
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
  ),
  'indexes' => 
  array (
    'lang' => 
    array (
      'columns' => 
      array (
        0 => 'lang',
      ),
      'unique' => false,
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
    'object_class' => 
    array (
      'columns' => 
      array (
        0 => 'object_class',
      ),
      'unique' => false,
      'fulltext' => false,
      'PRIMARY' => false,
    ),
    'object_id' => 
    array (
      'columns' => 
      array (
        0 => 'object_id',
      ),
      'unique' => false,
      'fulltext' => false,
      'PRIMARY' => false,
    ),
    'object_vers_field_hid' => 
    array (
      'columns' => 
      array (
        0 => 'object_class',
        1 => 'vers',
        2 => 'field',
        3 => 'lang',
        4 => 'hid',
      ),
      'unique' => true,
      'fulltext' => false,
      'PRIMARY' => false,
    ),
  ),
); 