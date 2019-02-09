<?php

return [
    [
        'id' => 'with_parent_route',
        'objects' => ['test_sharding_item'],
        'shard_groups' => [],
        'shard_id' => false,
        'adapter' => '\\Dvelum\\Orm\\Distributed\\Router\\WithParent',
        'config' => [
            'test_sharding_item' => [
                'parent' => 'test_sharding',
                'parent_field' => 'test_sharding'
            ]
        ],
        'enabled' => true
    ]
];