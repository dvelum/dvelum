<?php
return array(
  'theme'=>'default',
  'system_controllers' => array('Backend_Index_Controller' ,'Backend_Mediacategory_Controller', 'Backend_Vcs_Controller' , 'Backend_History_Controller', 'Backend_Permissions_Controller'),
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
			'Online',
			'Page',
			'Permissions',
			'User',
			'Vc',
			'Vote',
	        'Acl_simple',
	        'Mediacategory'
	),
	'use_csrf_token'=>true,
	// token lifetime seconds by default 2 hours 7200 s
	'use_csrf_token_lifetime'=>7200,
	// count of tokens to enable garbage collector
	'use_csrf_token_garbage_limit'=>500,
	// Code generator class
    'modules_generator'=>'Modules_Generator'
);