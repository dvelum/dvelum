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
    'bucket_field' => 'bucket',
    'routes' => 'sharding_routes.php',
    'shards' => 'sharding_shards.php',
    /*
     * Adapter for reserving primary keys
     */
    'key_generator' => '\\Dvelum\\Orm\\Distributed\\Key\\UniqueID',
    'sharding_types' => [
        'global_id' => [
            'title' => 'ORM_DISTRIBUTED_PRIMARY_KEY',
            'adapter' => '\\Dvelum\\Orm\\Distributed\\Key\\Strategy\\UniqueID',
        ],
        'sharding_key' =>[
            'title' => 'ORM_SHARD_KEY',
            'adapter' => '\\Dvelum\\Orm\\Distributed\\Key\\Strategy\\UserKey',
        ],
        'sharding_key_no_index'=>[
            'title' => 'ORM_SHARD_KEY_NO_INDEX',
            'adapter' => '\\Dvelum\\Orm\\Distributed\\Key\\Strategy\\UserKeyNoID',
        ],
        'virtual_bucket' =>[
            'title' => 'ORM_SHARD_VIRTUAL_BUCKET',
            'adapter' => '\\Dvelum\\Orm\\Distributed\\Key\\Strategy\\VirtualBucket',
        ]
    ],
    'keyToBucket' => [
        'number' => '\\Dvelum\\Orm\\Distributed\\Key\\Strategy\\VirtualBucket\\IntToBucket',
        'string' => '\\Dvelum\\Orm\\Distributed\\Key\\Strategy\\VirtualBucket\\StringToBucket',
    ],
];