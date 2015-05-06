<?php
return array(
    /*
     * Configuration Storage options
     */
    'config_storage' => array(
        'file_array'=>array(
            'paths' => array(
                './config/dist/',
                './config/local/',
            ),
            'write' =>  './config/local/',
            'apply_to' => './config/dist/',
        )
    ),
    // Autoloader config
    'autoloader' => array(
        'paths' => array(
            './system/library'
        )
    ),
);