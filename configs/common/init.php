<?php
return array(
    /*
     * Configuration Storage options
     */
    'config_storage' => array(
        'file_array'=>array(
            'paths' => array(
                './configs/common/',
                './application/configs/common/dist/',
                './application/configs/common/local/',
            ),
            'write' =>  './application/configs/common/local/',
            'apply_to' => './configs/common/',
        ),
        'debug' => false
    ),
    // Autoloader config
    'autoloader' => array(
        'paths' => array(
            './src'
        )
    ),
);