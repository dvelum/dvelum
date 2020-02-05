<?php return array (
    'table' => 'historylog',
    'engine' => 'InnoDB',
    'rev_control' => false,
    'link_title' => 'type',
    'save_history' => false,
    'system' => true,
    'disable_keys' => true,
    'fields' =>
        array (
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
            'date' =>
                array (
                    'db_len' => false,
                    'db_type' => 'datetime',
                    'db_isNull' => 1,
                    'required' => false,
                    'db_default' => NULL,
                ),
            'record_id' =>
                array (
                    'required' => true,
                    'db_type' => 'bigint',
                    'db_len' => 11,
                    'db_isNull' => 0,
                    'db_default' => 0,
                    'db_unsigned' => true,
                ),
            'type' =>
                array (
                    'type' => 'link',
                    'unique' => '',
                    'db_isNull' => false,
                    'required' => true,
                    'validator' => '',
                    'link_config' =>
                        array (
                            'link_type' => 'dictionary',
                            'object' => 'log_operation',
                        ),
                    'db_type' => 'varchar',
                    'db_len' => 255,
                    'db_default' => false,
                ),
            'object' =>
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
            'before' =>
                array (
                    'type' => '',
                    'unique' => '',
                    'db_isNull' => true,
                    'required' => false,
                    'validator' => '',
                    'db_type' => 'longtext',
                    'db_default' => false,
                    'is_search' => false,
                    'allow_html' => true,
                ),
            'after' =>
                array (
                    'type' => '',
                    'unique' => '',
                    'db_isNull' => true,
                    'required' => false,
                    'validator' => '',
                    'db_type' => 'longtext',
                    'db_default' => false,
                    'is_search' => false,
                    'allow_html' => true,
                ),
        ),
    'indexes' =>
        array (
            'date' =>
                array (
                    'columns' =>
                        array (
                            0 => 'date',
                        ),
                    'unique' => false,
                    'fulltext' => false,
                    'PRIMARY' => false,
                ),
            'user_id' =>
                array (
                    'columns' =>
                        array (
                            0 => 'user_id',
                        ),
                    'unique' => false,
                    'fulltext' => false,
                    'PRIMARY' => false,
                ),
            'object' =>
                array (
                    'columns' =>
                        array (
                            0 => 'object',
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
    'acl' => false,
    'parent_object' => '',
    'log_detalization' => 'default',
); 