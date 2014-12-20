<?php
return array(
    'gen_version'=>1,
	'versions'=>array(
	   '0.9.3.x'=>1  
    ),
    'locations'=>array(
		'./system/app',
        './system/library',
        './system/rewrite',
	),
    'exceptions'=>array(
    	'./system/library/Zend',
        './system/library/Spreadsheet'
    ),
    'hid_generator' => array(
        'adapter' => 'Sysdoc_Historyid',
    )
);