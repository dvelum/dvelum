<?php
/*
memcached example:
'backend' => array(
						'name' => 'Cache_Memcache',
						'options' => array(
								'compression' => 1,
								'normalizeKeys'=>0,
								'defaultLifeTime'=> 604800, // 7 days
								'keyPrefix'=>'dv_sys',
								'servers'=>array(
										array(
												'host' => 'localhost',
												'port' => 11211,
												'persistent' => true,
												'weight' => 1,
												'timeout' => 5,
												'retry_interval' => 15,
												'status' => true
										)
								)
						)
				)

memcached example:
'backend' => array(
						'name' => 'Cache_Memcached',
						'options' => array(
								'compression' => 1,
								'normalizeKeys'=>1,
								'defaultLifeTime'=> 604800, // 7 days
								'keyPrefix'=>'dv_sys',
                                'persistent_key' => 'some_key', // or false
								'servers'=>array(
										array(
												'host' => 'localhost',
												'port' => 11211,
												'weight' => 1,
										)
								)
						)
				)

APC example:
 'backend' => array(
						'name' => 'Cache_Apc',
						'options' => array(
								'normalizeKeys'=>0,
								'defaultLifeTime'=> 604800, // 7 days
								'keyPrefix'=>'dv_sys',
						)
				)
 */
return array(
	 	// Frontend data cache
		'data'=>array(
				'enabled'=>1,
				'backend' => array(
						'name' => 'Cache_Memcached',
						'options' => array(
								'compression' => 1,
								'normalizeKeys'=>1,
								'defaultLifeTime'=> 604800, // 7 days
								'keyPrefix'=>'dv_dat',
							    'persistent_key' => 'dv_cache_',
								'servers'=>array(
										array(
												'host' => 'localhost',
												'port' => 11211,
												'weight' => 1,
										)
								)
						)
				)
		)
);