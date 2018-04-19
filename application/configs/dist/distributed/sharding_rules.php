<?php
return [
  'object_to_shard' =>[
      'description' => 'Objects stores at specific shard group',
      'adapter' => '\\Dvelum\\ORM\\Shardig\\Rule\\ObjectToShard',
      'options' => [
           'shardGroup' => 'A',
           'objects' => [
                'user'
           ]
      ],
      'enabled' => false
  ],
  'client_data_to_shard' =>[
        'description' => 'All client data stores at one shard',
        'adapter' => '\\Dvelum\\ORM\\Shardig\\Rule\\ClientToShard',
        'options' => [
            'shard' => 'A',
            'objects' => [
                'user_settings' => [
                    'owner_link' => 'user_id'
                ],
                'user_orders' => [
                    'owner_link' => 'user_id'
                ]
            ]
        ],
        'enabled' => false
  ],
];