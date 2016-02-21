<?php
return array(
	'storage'=>'file',
	'configs'=> 'layouts/',
	'lang'=>'eng',
	'development'=>false,
	'components'=>'Ext/Component',
	'field_components'=>'Ext/Component/Field',
	'filter_conponents'=>'Ext/Component/Filter',
	'actionjs_path'=>'www/js/app/actions/',
	'compiled_js'=>'js/app/system/Designer.js',
    'langs_path'=>'www/js/lang/',
    'langs_url'=>'/js/lang/',
	'js_path'=>'www/js/',
	'js_url'=>'/js/',
	'templates' => array(
		'wwwroot' => '[%wroot%]',
		'adminpath' => '[%admp%]',
		'urldelimiter' => '[%-%]'
	),
	'vcs_support' => false,
	'theme'=>'gray',
	'application_theme' => 'gray',

);
