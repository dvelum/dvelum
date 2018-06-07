<?php
return array(
    'source_id' =>
        array (
            'type' => 'link',
            'title' => 'SOURCE',
            'unique' => 'source_target',
            'db_isNull' => true,
            'required' => true,
            'validator' => '',
            'link_config' =>
                array (
                    'link_type' => 'object',
                    'object' => 'group',
                ),
            'db_type' => 'bigint',
            'db_default' => false,
            'db_unsigned' => true,
     ),
    'target_id' =>
        array (
            'type' => 'link',
            'title' => 'TARGET',
            'unique' => 'source_target',
            'db_isNull' => true,
            'required' => true,
            'validator' => '',
            'link_config' =>
                array (
                    'link_type' => 'object',
                    'object' => 'group',
                ),
            'db_type' => 'bigint',
            'db_default' => false,
            'db_unsigned' => true,
        ),
    'order_no' =>
        array (
            'db_type' => 'int',
            'title' => 'SORT',
            'db_len' => 10,
            'db_isNull' => false,
            'db_default' => 0,
            'db_unsigned' => true,
        ),
);