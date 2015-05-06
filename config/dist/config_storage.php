<?php
/*
 * Configuration Storage options
 */
return array(
    'file_array'=>array(
        'paths' => array(
            './config/dist/',
            './config/local/',
        ),
        'write' =>  './config/local/',
        'apply_to' => './config/dist/',
    )
);