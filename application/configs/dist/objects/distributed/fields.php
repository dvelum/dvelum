<?php
return [
    'shard'=> [
        'type' => 'link',
        'title' => 'SHARD',
        'unique' => false,
        'db_isNull' => false,
        'required' => true,
        'validator' => '',
        'link_config' =>[
            'link_type' => 'object',
            'object' => 'distributed_shard',
        ],
        'db_type' => 'bigint',
        'db_default' => false,
        'db_unsigned' => true,
        'system'=>true,
        'lazyLang'=>true
    ]
];
