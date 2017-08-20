<?php

return [
    // PSR-0 autoload paths
    'paths' => [
        './application/src',
        './application/library',
        './dvelum/app',
        './dvelum/library',
        './dvelum2'
    ],
    // paths priority (cannot be overridden by external modules)
    'priority'=>[
        './application/controllers',
        './application/models',
        './application/library',
    ],
    // Use class maps
    'useMap' => true,
    // Use class map (Reduce IO load during autoload)
    // Class map file path (string / false)
    'map' => 'classmap.php',
    // PSR-4 autoload paths
    'psr-4' =>[
        'Psr\\Log'=>'./vendor/psr/log/Psr/Log',
        'Zend\\Stdlib' => './vendor/zendframework/zend-stdlib/src',
        'Zend\\Db' => './vendor/zendframework/zend-db/src'
    ],
    // Paths to be excluded from class map
    'noMap' =>[

    ]
];