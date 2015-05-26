<?php return array (
  'table' => 'vc',
  'engine' => 'InnoDB',
  'rev_control' => false,
  'save_history' => false,
  'system' => true,
  'fields' => 
  array (
    'date' => 
    array (
      'required' => 1,
      'db_type' => 'datetime',
      'db_isNull' => 0,
    ),
    'record_id' => 
    array (
      'required' => 1,
      'db_type' => 'int',
      'db_len' => 11,
      'db_isNull' => 0,
      'db_default' => 0,
      'db_unsigned' => true,
    ),
    'object_name' => 
    array (
      'required' => 1,
      'db_type' => 'varchar',
      'db_len' => 100,
      'db_isNull' => 0,
      'db_default' => '',
    ),
    'data' => 
    array (
      'required' => 1,
      'allow_html' => true,
      'db_type' => 'longtext',
      'db_isNull' => 0,
      'db_default' => '',
    ),
    'user_id' => 
    array (
      'required' => true,
      'type' => 'link',
      'link_config' => 
      array (
        'link_type' => 'object',
        'object' => 'user',
      ),
      'db_type' => 'bigint',
      'db_isNull' => false,
      'db_default' => false,
      'db_unsigned' => true,
    ),
    'version' => 
    array (
      'type' => '',
      'unique' => false,
      'db_isNull' => false,
      'required' => true,
      'db_type' => 'bigint',
      'db_default' => 0,
      'db_unsigned' => true,
    ),
  ),
  'link_title' => 'object_name',
  'indexes' => 
  array (
    'object_record_version' => 
    array (
      'columns' => 
      array (
        0 => 'object_name',
        1 => 'record_id',
        2 => 'version',
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
); 