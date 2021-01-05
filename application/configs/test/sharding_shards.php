<?php
return [
    [
        'id' => 'shard1',
        'host' => '127.0.0.1',
        'group' => 'default',
        'weight'=> 6,
        'override' =>[
            'prefix' => 'shard1_',
        ],
    ], [
        'id' => 'shard2',
        'host' => '127.0.0.1',
        'group' => 'default',
        'weight'=> 6,
        'override' =>[
            'prefix' => 'shard2_',
        ],
    ], [
        'id' => 'shard3',
        'host' => '127.0.0.1',
        'group' => 'default',
        'weight'=> 6,
        'override' =>[
            'prefix' => 'shard3_',
        ],
    ],
];
