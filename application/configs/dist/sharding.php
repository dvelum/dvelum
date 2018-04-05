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
    /*
     * Shards Object
     */
    'shard_object' => 'distributed_shard',
    'shard_field' => 'shard',
    /*
     * Adapter for reserving primary keys
     */
    'key_generator' => '\\Dvelum\\Orm\\Sharding\\Key\\OrmIndex'
];