<?php
return [
    [
        'id' => '',
        'title' => '',
        'objects' => [],
        'shard_groups' => [],
        'shard_id' => false,
        'adapter' => '\\Dvelum\\Orm\\Distributed\\Router\\WithParent',
        'config' => [
            'order_item' => [
                'parent' => 'order',
                'parent_field' => 'order_id'
            ],
            'user_settings' => [
                'parent' => 'user',
                'parent_field' => 'user_id'
            ]
        ],
        'enabled' => false
    ]
];