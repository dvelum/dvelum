<?php
return array(
		'date_created'=>array(
				'title' => 'DATE_CREATED',
				'db_type' => 'datetime',
				'db_len' => false,
				'db_isNull' => true,
				'db_default' =>null,
				'system'=>true,
				'lazyLang'=>true
		),
		'date_published'=>array(
				'title' => 'DATE_PUBLISHED',
				'db_type' => 'datetime',
				'db_len' => false,
				'db_isNull' => true,
				'db_default' =>null,
				'system'=>true,
				'lazyLang'=>true
		),
		'date_updated' =>array (
				'title' =>  'DATE_UPDATED',
				'db_len' => false,
				'db_type' => 'datetime',
				'db_isNull' => true,
				'db_default' =>null,
				'system'=>true,
				'lazyLang'=>true
		),
		'author_id' =>array(
				'required' => true,
				'title' => 'CREATED_BY',
				'type'=>'link',
				'link_config'=>array(
						'link_type'=>'object',
						'object'=>'user',
				),
				'system'=>true,
				'db_type' => 'bigint',
				'db_isNull' => false,
				'db_unsigned' => true,
				'lazyLang'=>true
		),
		'editor_id' =>array(
				'required' => false,
				'title' => 'UPDATED_BY',
				'type'=>'link',
				'link_config'=>array(
						'link_type'=>'object',
						'object'=>'user',
				),
				'system'=>true,
				'db_type' => 'bigint',
				'db_isNull' => true,
				'db_unsigned' => true,
				'lazyLang'=>true
		),
		'published' =>array(
				'title' => 'PUBLISHED',
				'db_type' => 'boolean',
				'db_isNull' => false,
				'db_default' =>0,
				'system'=>true,
				'lazyLang'=>true
		) ,
		'published_version' =>array (
				'title' => 'PUBLISHED_VERSION',
				'db_type' => 'bigint',
				'db_len' => 20,
				'db_isNull' =>false,
				'db_default' =>0,
				'db_unsigned'=>true,
				'system'=>true,
				'lazyLang'=>true
		) ,
		'last_version' =>array (
				'title' => 'LAST_VERSION',
				'db_type' => 'bigint',
				'db_len' => 20,
				'db_isNull' =>false,
				'db_default' =>0,
				'db_unsigned'=>true,
				'system'=>true,
				'lazyLang'=>true
		)
);

