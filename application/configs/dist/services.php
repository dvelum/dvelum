<?php
return [
    'ORM' => [
        'loader'=>'\\Dvelum\\App\\Service\\Loader\\Orm'
    ],
    'Lang' => [
        'loader'=>'\\Dvelum\\App\\Service\\Loader\\Lang'
    ],
    'Dictionary' => [
        'loader'=>'\\Dvelum\\App\\Service\\Loader\\Dictionary'
    ],
    'BlockManager' => [
        'loader'=>'\\Dvelum\\App\\Service\\Loader\\BlockManager'
    ],
    'MailTransport' => [
        'loader'=>'\\Dvelum\\App\\Service\\Loader\\MailTransport'
    ],
    'Template' => [
        'loader'=>'\\Dvelum\\App\\Service\\Loader\\Template'
    ],
    'ShardingRouter' =>[
        'loader' => '\\Dvelum\\App\\Service\\Loader\\DistributedRoutes'
    ]
];