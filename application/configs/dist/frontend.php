<?php
return array(
    'use_csrf_token'=>false,
    // token lifetime seconds by default 2 hours 7200 s
    'use_csrf_token_lifetime'=>7200,
    // count of tokens to enable garbage collector
    'use_csrf_token_garbage_limit'=>500,
    // ExtJS theme for Frontend_Backoffice Controller
    'backoffice_extjs_theme' => 'gray',
    /*
     * the type of frontend router with two possible values:
     * 'Module' — using tree-like page structure  (‘Pages’ section of the back-office panel);
     * 'Path' — the router based on the file structure of client controllers.
     * 'Config' - using frontend modules configuration
     */
    'router' => 'Module', // 'Module','Path','Config'
    // Default Frontend Controller
    'default_controller' => 'Dvelum\\App\\Frontend\\Index',
);