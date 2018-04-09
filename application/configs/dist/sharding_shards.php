<?php
return [
    [
        'id' => 'Shard1',
        'host' => '127.0.0.1',
        'group' => 'default',
        'weight'=> 10
    ],
    [
        'id' => 'Shard2',
        'weight' => 2,
        'host' => '127.0.0.2',
        'group' => 'default',
    ],
    [
        'id' => 'Shard3',
        'weight' => 0,
        'host' => '127.0.0.3',
        'group' => 'default',
    ]
];