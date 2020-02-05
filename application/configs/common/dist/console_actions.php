<?php
/*
 *  Console actions
 */
return [
    // Rebuild database
    'buildDb'=>[
        'adapter' => '\\Dvelum\\App\\Console\\Orm\\Build'
    ],
    // Rebuild database
    'buildShards'=>[
        'adapter' => '\\Dvelum\\App\\Console\\Orm\\BuildShards'
    ],
    // Create Model classes
    'generateModels'=>[
        'adapter' => '\\Dvelum\\App\\Console\\Generator\\Models'
    ],
    // Create Model classes
    'generateClassMap'=>[
        'adapter' => '\\Dvelum\\App\\Console\\Generator\\ClassMap'
    ],
    // Rebuild JS lang files
    'buildJs'=>[
        'adapter' => '\\Dvelum\\App\\Console\\Js\\Lang'
    ],
    // Clear memory tables used for Background tasks
    'clearMemory'=>[
        'adapter' => '\\Dvelum\\App\\Console\\Clear\\Memory'
    ],
    // clear js and css cache
    'clearStatic'=>[
        'adapter' => '\\Dvelum\\App\\Console\\Clear\\StaticCache'
    ],
    //  external add
    'external-add' =>[
        'adapter' => '\\Dvelum\\App\\Console\\External\\Add'
    ]
];
