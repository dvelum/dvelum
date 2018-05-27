<?php
return [
    /*
     * Use index object for distributed Records
     */
    'dist_index_enabled' => true,
    /*
     * Postfix for distributed indexes
     */
    'dist_index_postfix' => '_dist_index',
    /*
     * Default connection for distributed index object
     */
    'dist_index_connection' => 'sharding_index',
    'shard_field' => 'shard',
    'routes' => 'sharding_routes.php',
    'shards' => 'sharding_shards.php',
    /*
     * Adapter for reserving primary keys
     */
    'key_generator' => '\\Dvelum\\Orm\\Distributed\\Key\\OrmIndex',
    'sharding_types' => [
        'global_id' => [
            'title' => 'ORM_DISTRIBUTED_PRIMARY_KEY',
            'adapter' => '\\Dvelum\\Orm\\Distributed\\Key\\OrmIndex',
        ],
        'sharding_key' =>[
            'title' => 'ORM_SHARD_KEY',
            'adapter' => '\\Dvelum\\Orm\\Distributed\\Key\\ShardingKey',
        ]
    ]
];