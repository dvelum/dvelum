<?php
/*
 * Configuration Storage options
 */
return array(
    'file_array'=>array(
        'paths' => array(
            './configs/common/',
            './application/configs/common/dist/',
            './application/configs/common/local/',
        ),
        'write' =>  './application/configs/common/local/',
        'apply_to' => './application/configs/common/dist/',
    ),
    'debug'=>false
);