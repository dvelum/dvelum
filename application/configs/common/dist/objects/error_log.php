<?php return array (
    'table' => 'error_log',
    'engine' => 'InnoDB',
    'connection' => 'error',
    'rev_control' => false,
    'save_history' => false,
    'link_title' => '',
    'disable_keys' => false,
    'readonly' => false,
    'locked' => false,
    'primary_key' => 'id',
    'use_db_prefix' => true,
    'system' => true,
    'fields' =>
        array (
            'name' =>
                array (
                    'type' => '',
                    'unique' => '',
                    'db_isNull' => false,
                    'required' => true,
                    'validator' => '',
                    'db_type' => 'varchar',
                    'db_default' => '',
                    'db_len' => 255,
                    'is_search' => false,
                    'allow_html' => false,
                ),
            'message' =>
                array (
                    'type' => '',
                    'unique' => '',
                    'db_isNull' => true,
                    'required' => false,
                    'validator' => '',
                    'db_type' => 'longtext',
                    'db_default' => false,
                    'is_search' => false,
                    'allow_html' => false,
                ),
            'date' =>
                array (
                    'type' => '',
                    'unique' => '',
                    'db_isNull' => false,
                    'required' => true,
                    'validator' => '',
                    'db_type' => 'datetime',
                    'db_default' => false,
                ),
            'level' =>
                array (
                    'type' => '',
                    'unique' => '',
                    'db_isNull' => false,
                    'required' => true,
                    'validator' => '',
                    'db_type' => 'varchar',
                    'db_default' => '',
                    'db_len' => 255,
                    'is_search' => false,
                    'allow_html' => false,
                ),
            'context' =>
                array (
                    'type' => '',
                    'unique' => '',
                    'db_isNull' => true,
                    'required' => false,
                    'validator' => '',
                    'db_type' => 'longtext',
                    'db_default' => false,
                    'is_search' => false,
                    'allow_html' => false,
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
            'name' =>
                array (
                    'columns' =>
                        array (
                            0 => 'name',
                        ),
                    'unique' => false,
                    'fulltext' => false,
                    'PRIMARY' => false,
                ),
        ),
    'acl' => false
); 