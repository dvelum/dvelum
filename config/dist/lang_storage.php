<?php
/*
 * Configuration Storage options
 */
return array(
    'file_array'=>array(
        'paths' => array(
            './lang/dist/',
            './lang/local/',
        ),
        'write' =>  './config/local/',
        'apply_to' => './lang/dist/',
    ),
    'debug'=>false
);