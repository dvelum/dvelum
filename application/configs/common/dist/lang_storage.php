<?php
/*
 * Configuration Storage options
 */
return array(
    'file_array'=>array(
        'paths' => array(
            './application/locales/dist/',
            './application/locales/local/',
        ),
        'write' =>  './application/locales/local/',
        'apply_to' => './application/locales/dist/',
    ),
    'debug'=>false
);