<?php
return [
    [
        'id' => 'shard1',
        'host' => 'localhost',
        'group' => 'default',
        'weight'=> 6,
        'override' =>[
            'dbname' => 'dvelum_test_sh1'
        ],
    ], [
        'id' => 'shard2',
        'host' => 'localhost',
        'group' => 'default',
        'weight'=> 6,
        'override' =>[
            'dbname' => 'dvelum_test_sh2'
        ],
    ],
];
