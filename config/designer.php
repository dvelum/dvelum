<?php
return array(
	'storage'=>'file',
	'configs'=> '../config/layouts/',
	'lang'=>'eng',
	'connections'=> '../DesignerConnections.php',
	'development'=>true,
	'components'=>'./system/library/Ext/Component',
	'field_components'=>'./system/library/Ext/Component/Field',
	'filter_conponents'=>'./system/library/Ext/Component/Filter',
	'controllers'=>'./system/app/',
	'actionjs_path'=>'./js/app/actions/',
	'compiled_js'=>'/js/app/system/Designer.js',
    'langs_path'=>'./js/lang/',
    'langs_url'=>'/js/lang/',
	'js_path'=>'./js/',
	'js_url'=>'/js/',
	'templates' => array(
		'wwwroot' => '[%wroot%]',
		'adminpath' => '[%admp%]',
		'urldelimiter' => '[%-%]'
	)
);