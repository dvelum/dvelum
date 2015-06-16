<?php
/*
 * Configuration Storage options
 */
return array(
    'file_array'=>array(
        'paths' => array(
            './application/configs/dist/',
            './application/configs/local/',
        ),
        'write' =>  './application/configs/local/',
        'apply_to' => './application/configs/dist/',
    ),
    'debug'=>false
);