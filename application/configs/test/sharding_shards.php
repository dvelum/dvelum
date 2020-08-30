<?php
return [
    [
        'id' => 'shard1',
        'host' => 'localhost',
        'group' => 'default',
        'weight'=> 6,
        'override' =>[
            'prefix' => 'shard1_',
        ],
    ], [
        'id' => 'shard2',
        'host' => 'localhost',
        'group' => 'default',
        'weight'=> 6,
        'override' =>[
            'prefix' => 'shard2_',
        ],
    ], [
        'id' => 'shard3',
        'host' => 'localhost',
        'group' => 'default',
        'weight'=> 6,
        'override' =>[
            'prefix' => 'shard3_',
        ],
    ],
];
