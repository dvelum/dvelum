<?php

return [
    // Paths for autoloading
    'paths' => array(
        './application/controllers',
        './application/models',
        './application/library',
        './dvelum/app',
        './dvelum/library',
        './vendor'
    ),
    // priority paths (cannot be overridden by external modules)
    'priority'=>[
        './application/controllers',
        './application/models',
        './application/library',
    ],
    /*
    * Use class maps
    */
    'useMap' => false,
    // Use class map (Reduce IO load during autoload)
    // Class map file path (string / false)
    'map' => 'classmap.php',
    'plugins' =>[
          'psr-4' =>[
            'class' => 'Autoloader_Psr4',
            'config' => [
                'paths' => [
                    'Zend\\Stdlib' => './vendor/zendframework/zend-stlib/src',
                    'Zend\\Db' => './vendor/zendframework/zend-db/src',
                ]
            ]
        ]
    ]
];