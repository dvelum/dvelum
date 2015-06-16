<?php
return array(
    /*
     * Configuration Storage options
     */
    'config_storage' => array(
        'file_array'=>array(
            'paths' => array(
                './application/configs/dist/',
                './application/configs/local/',
            ),
            'write' =>  './application/configs/local/',
            'apply_to' => './application/configs/dist/',
        ),
        'debug' => false
    ),
    // Autoloader config
    'autoloader' => array(
        'paths' => array(
            './dvelum/library'
        )
    ),
);