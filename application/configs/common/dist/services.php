<?php
return [
    'ORM' => [
        'loader'=>'\\Dvelum\\App\\Service\\Loader\\Orm'
    ],
    'BlockManager' => [
        'loader'=>'\\Dvelum\\App\\Service\\Loader\\BlockManager'
    ],
    'ShardingRouter' =>[
        'loader' => '\\Dvelum\\App\\Service\\Loader\\DistributedRoutes'
    ]
];