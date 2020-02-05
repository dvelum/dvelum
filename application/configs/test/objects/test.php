<?php return array (
    'table' => 'test',
    'engine' => 'InnoDB',
    'connection' => 'default',
    'rev_control' => false,
    'save_history' => true,
    'link_title' => '',
    'disable_keys' => false,
    'readonly' => false,
    'locked' => false,
    'primary_key' => 'id',
    'use_db_prefix' => true,
    'fields' =>
        array (
            'integer' =>
                array (
                    'type' => '',
                    'unique' => '',
                    'db_isNull' => true,
                    'required' => false,
                    'validator' => '',
                    'db_type' => 'bigint',
                    'db_default' => 0,
                    'db_unsigned' => false,
                ),
            'varchar' =>
                array (
                    'type' => '',
                    'unique' => '',
                    'db_isNull' => true,
                    'db_len' => 255,
                    'required' => false,
                    'validator' => '',
                    'db_type' => 'varchar',
                    'db_default' => '',
                    'is_search'=> true,
                    'db_unsigned' => false,
                ),
            'float' =>
                array (
                    'type' => '',
                    'unique' => '',
                    'db_isNull' => false,
                    'required' => true,
                    'validator' => '',
                    'db_type' => 'float',
                    'db_default' => 0,
                    'db_unsigned' => true,
                    'db_scale' => 10,
                    'db_precision' => 5,
                ),
            'link' =>
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
            'multilink' =>
                array (
                    'type' => 'link',
                    'unique' => '',
                    'db_isNull' => 0,
                    'required' => false,
                    'validator' => '',
                    'link_config' =>
                        array (
                            'link_type' => 'multi',
                            'object' => 'page',
                        ),
                    'db_type' => 'longtext',
                    'db_default' => '',
                ),
            'dictionary' =>
                array (
                    'type' => 'link',
                    'unique' => '',
                    'db_isNull' => false,
                    'required' => true,
                    'validator' => '',
                    'link_config' =>
                        array (
                            'link_type' => 'dictionary',
                            'object' => 'link_type',
                        ),
                    'db_type' => 'varchar',
                    'db_len' => 255,
                ),
        ),
    'indexes' =>
        array (
        ),
);