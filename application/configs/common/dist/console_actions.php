<?php
/*
 *  Console actions
 */
return [
    // Create class map
    'generateClassMap'=>[
        'adapter' => \Dvelum\App\Console\Generator\PlatformClassMap::class
    ],
    // Rebuild database
    'buildDb'=>[
        'adapter' => \Dvelum\App\Console\Orm\Build::class
    ],
    // Rebuild database
    'buildShards'=>[
        'adapter' => \Dvelum\App\Console\Orm\BuildShards::class
    ],
    // Create Model classes
    'generateModels'=>[
        'adapter' => \Dvelum\App\Console\Generator\Models::class
    ],
    // Rebuild JS lang files
    'buildJs'=>[
        'adapter' => \Dvelum\App\Console\Js\Lang::class
    ],
    // Clear memory tables used for Background tasks
    'clearMemory'=>[
        'adapter' => \Dvelum\App\Console\Clear\Memory::class
    ],
    //  external add
    'external-add' =>[
        'adapter' => \Dvelum\App\Console\External\Add::class
    ]
];
