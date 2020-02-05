<?php
return array(
    'theme' => 'gray', // gray / triton / crisp / desktop / aria / classic
    'desktop_themes' => ['desktop'],
    'themes' => [
        'gray',
        'triton',
        'crisp',
        'aria',
        'classic',
        'desktop'
    ],
    'languages' => [
        'en',
        'ru'
    ],
    'system_controllers' => [
        '\\Dvelum\\App\\Backend\\Index\\Controller',
        '\\Dvelum\\App\\Backend\\Permissions\\Controller',
        '\\Dvelum\\App\\Backend\\Vcs\\Controller',
        '\\Dvelum\\App\\Backend\\History\\Controller',
        '\\Dvelum\\App\\Backend\\Login\\Controller'
    ],
    //reserved object names
    'system_objects' => [
        'Bgtask',
        'Bgtask_signal',
        'Group',
        'Historylog',
        'Links',
        'Permissions',
        'User',
        'Vc',
        'Acl_simple',
    ],
    'use_csrf_token' => 0,
    // token lifetime seconds by default 2 hours 7200 s
    'use_csrf_token_lifetime' => 7200,
    // count of tokens to enable garbage collector
    'use_csrf_token_garbage_limit' => 500,
    // Code generator class
    'modules_generator' => '\\Dvelum\\App\\Module\\Generator\\Simple',
    // Menu adapter class for classic theme
    'menu_adapter' => '\\Dvelum\\App\\Menu',
    // Menu adapter class for desktop theme
    'desktop_menu_adapter' => '\\Dvelum\\App\\Menu\\Desktop',
    // Modules in that need disable menu rendering
    'modules_without_menu' => ['Designer']
);