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
    // Rebuild JS lang files
    'buildJs'=>[
        'adapter' => '\\Dvelum\\App\\Console\\Js\\Lang'
    ],
    // Clear memory tables used for Background tasks
    'clearMemory'=>[
        'adapter' => '\\Dvelum\\App\\Console\\Clear\\Memory'
    ],
    //  external add
    'external-add' =>[
        'adapter' => '\\Dvelum\\App\\Console\\External\\Add'
    ]
];
