<?php
return array(
	'storage'=>'file',
	'configs'=> 'layouts/',
	'lang'=>'eng',
	'connections'=> 'DesignerConnections.php',
	'development'=>true,
	'components'=>'./dvelum/library/Ext/Component',
	'field_components'=>'./dvelum/library/Ext/Component/Field',
	'filter_conponents'=>'./dvelum/library/Ext/Component/Filter',
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
	'theme'=>'gray'
);