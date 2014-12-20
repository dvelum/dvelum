<?php return array (
  'table' => 'links',
  'engine' => 'InnoDB',
  'rev_control' => false,
  'save_history' => false,
  'system' => true,
  'fields' => 
  array (
    'src' => 
    array (
      'required' => 1,
      'db_type' => 'varchar',
      'db_len' => 100,
      'db_isNull' => false,
      'unique' => 'uniq_group',
    ),
    'src_id' => 
    array (
      'required' => 1,
      'db_type' => 'bigint',
      'db_len' => 11,
      'db_isNull' => 0,
      'db_unsigned' => true,
      'unique' => 'uniq_group',
    ),
    'src_field' => 
    array (
      'required' => 1,
      'db_type' => 'varchar',
      'db_len' => 100,
      'db_isNull' => false,
      'unique' => 'uniq_group',
    ),
    'target' => 
    array (
      'required' => 1,
      'db_type' => 'varchar',
      'db_len' => 100,
      'db_isNull' => false,
      'unique' => 'uniq_group',
    ),
    'target_id' => 
    array (
      'required' => 1,
      'db_type' => 'bigint',
      'db_len' => 11,
      'db_isNull' => 0,
      'db_unsigned' => true,
      'unique' => 'uniq_group',
    ),
    'order' => 
    array (
      'required' => 1,
      'db_type' => 'int',
      'db_len' => 4,
      'db_isNull' => 0,
      'db_unsigned' => true,
      'db_default' => 0,
    ),
  ),
  'indexes' => 
  array (
    'src_src_id' => 
    array (
      'columns' => 
      array (
        0 => 'src',
        1 => 'src_id',
      ),
      'unique' => false,
      'fulltext' => false,
      'PRIMARY' => false,
    ),
    'target_target_id' => 
    array (
      'columns' => 
      array (
        0 => 'target',
        1 => 'target_id',
      ),
      'unique' => false,
      'fulltext' => false,
      'PRIMARY' => false,
    ),
  ),
  'connection' => 'default',
  'locked' => false,
  'readonly' => false,
  'primary_key' => 'id',
  'use_db_prefix' => true,
  'link_title' => 'id',
  'disable_keys' => false,
); 