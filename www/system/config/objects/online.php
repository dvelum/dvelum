<?php return array (
  'table' => 'online',
  'engine' => 'Memory',
  'rev_control' => false,
  'link_title' => 'ssid',
  'save_history' => false,
  'system' => true,
  'fields' => 
  array (
    'ssid' => 
    array (
      'type' => '',
      'unique' => false,
      'db_isNull' => false,
      'required' => true,
      'db_type' => 'char',
      'db_default' => '',
      'db_len' => 32,
      'is_search' => false,
      'allow_html' => false,
    ),
    'update_time' => 
    array (
      'db_type' => 'timestamp',
      'db_isNull' => false,
      'required' => true,
    ),
    'user_id' => 
    array (
      'type' => 'link',
      'link_config' => 
      array (
        'link_type' => 'object',
        'object' => 'user',
      ),
      'required' => true,
      'db_type' => 'bigint',
      'db_isNull' => false,
      'db_default' => false,
      'db_unsigned' => true,
    ),
  ),
  'indexes' => 
  array (
    'ssid' => 
    array (
      'columns' => 
      array (
        0 => 'ssid',
      ),
      'unique' => true,
      'fulltext' => false,
      'PRIMARY' => false,
    ),
  ),
  'connection' => 'default',
  'locked' => false,
  'readonly' => false,
  'disable_keys' => false,
  'primary_key' => 'id',
  'use_db_prefix' => true,
); 