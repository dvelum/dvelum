<?php
return array(
    /*
     * the type of frontend router with two possible values:
     * 'Path' — the router based on the file structure of client controllers.
     * 'Config' - using frontend modules configuration
     */
    'router' => 'Config', // 'Path','Config'
    // Default Frontend Controller
    'default_controller' => '\\Dvelum\\App\\Frontend\\Index\\Controller',
);