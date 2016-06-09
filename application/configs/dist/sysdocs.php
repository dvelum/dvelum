<?php
return array(
    'gen_version'=>9,
	'versions'=>array(
       '1.0.0'=>8
    ),
    'default_languge'=> 'ru',
    'default_version' => '1.0.0',
    'locations'=>array(
        './application',
		'./dvelum'
	),
    'skip'=>array(
        './application/configs',
        './application/locales',
        './application/templates',
        './dvelum/templates'
    ),
    'hid_generator' => array(
        'adapter' => 'Sysdocs_Historyid',
    ),
    'fields' => array(
      'sysdocs_class' => array(
          'description'
      ),
      'sysdocs_class_method' => array(
          'description',
          'returnType'
      ),
      'sysdocs_class_method_param' => array(
          'description'
      ),
      'sysdocs_class_property' => array(
          'description',
          'type'
      ),
    )
);