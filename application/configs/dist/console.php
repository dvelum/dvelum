<?php
return [
    'router' => '\\Dvelum\\App\\Router\\Console',
    'user_id'=>1,
    'lockConfig'=>[
        'time_limit'=> 300,
        'intercept_timeout'=>300,
        'locks_dir'=> './data/locks/',
    ],
    'log' => [
        'enabled' => true,
        'type'=>'file',
        'logFile'=>'./data/logs/cronjobs.log'
    ]
];