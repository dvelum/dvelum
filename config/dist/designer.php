<?php
return array(
	'storage'=>'file',
	'configs'=> 'layouts/',
	'lang'=>'eng',
	'connections'=> 'DesignerConnections.php',
	'development'=>true,
	'components'=>'./system/library/Ext/Component',
	'field_components'=>'./system/library/Ext/Component/Field',
	'filter_conponents'=>'./system/library/Ext/Component/Filter',
	'controllers'=>'./system/app/',
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
	)
);