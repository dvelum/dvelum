<?php
return array(
	'theme'=>'gray', // gray / triton / crisp / desktop
    'desktop_themes' => ['desktop'],
	'system_controllers' => array(
		'Backend_Index_Controller' ,
		'Backend_Mediacategory_Controller',
		'Backend_Vcs_Controller' ,
		'Backend_History_Controller',
		'Backend_Permissions_Controller'
	),
	//reserved object names
	'system_objects'=>array(
		'Bgtask' ,
		'Bgtask_signal',
		'Blockmapping',
		'Blocks',
		'Comment',
		'Group',
		'Historylog',
		'Links',
		'Medialib',
		'Menu_item',
		'Menu',
		'Page',
		'Permissions',
		'User',
		'Vc',
		'Acl_simple',
		'Mediacategory',
		'Sysdocs_class',
		'Sysdocs_class_method',
		'Sysdocs_class_method_param',
		'Sysdocs_class_property',
		'Sysdocs_file',
		'Sysdocs_localization'

	),
	'use_csrf_token'=>true,
	// token lifetime seconds by default 2 hours 7200 s
	'use_csrf_token_lifetime'=>7200,
	// count of tokens to enable garbage collector
	'use_csrf_token_garbage_limit'=>500,
	// Code generator class
	'modules_generator'=>'Modules_Generator'
);