<?php

return [
    // PSR-0 autoload paths
    'paths' => [
        './application/controllers',
        './application/classes',
        './dvelum/src',
        './extensions/dvelum-orm/src',
        './extensions/dvelum-core/src'
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

        'Dvelum\\Db' => './vendor/dvelum/db/src',
        'Dvelum\\Cache' => './vendor/dvelum/cache/src',

        'Laminas\\Stdlib' => './vendor/laminas/zend-stdlib/src',
        'Laminas\\Db' => './vendor/laminas/zend-db/src',
        'Laminas\\Mail' => './vendor/laminas/zend-mail/src',
        'Laminas\\Mime' => './vendor/laminas/zend-mime/src',
        'Laminas\\Validator' => './vendor/laminas/zend-validator/src',
        'Laminas\\Loader' => './vendor/laminas/zend-loader/src',

        'MatthiasMullie\\Minify' => './vendor/matthiasmullie/minify/src',
        'MatthiasMullie\\PathConverter' => './vendor/matthiasmullie/path-converter/src'
    ],
    // Paths to be excluded from class map
    'noMap' =>[

    ]
];