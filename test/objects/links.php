<?php
return array(
        'table'=>'links',
        'engine'=>'InnoDb',
		'rev_control' =>false,
		'save_history'=>false,
		'system'=>true,
        'fields' => array(
 									'src' =>array (
                                            'required'=>1,
                                            'db_type' => 'varchar',
											'db_len' => 100,
                                            'db_isNull' => false,
                                            'unique'=>'uniq_group'
                                   ),
                                    'src_id' =>array(
                                   			'required'=>1,
                                            'db_type' => 'bigint',
                                            'db_len' => 11,
                                            'db_isNull' => 0,
                                            'db_unsigned'=>true,
                                    		'unique'=>'uniq_group'
                                   ),'src_field' =>array (
                                            'required'=>1,
                                            'db_type' => 'varchar',
											'db_len' => 100,
                                            'db_isNull' => false,
                                            'unique'=>'uniq_group'
                                   ),
                                   'target' =>array (
                                            'required'=>1,
                                            'db_type' => 'varchar',
											'db_len' => 100,
                                            'db_isNull' => false,
                                            'unique'=>'uniq_group'
                                   ),
                                    'target_id' =>array(
                                   			'required'=>1,
                                            'db_type' => 'bigint',
                                            'db_len' => 11,
                                            'db_isNull' => 0,
                                            'db_unsigned'=>true,
                                    		'unique'=>'uniq_group'
                                   ),
                                    'order' =>array(
                                   			'required'=>1,
                                            'db_type' => 'int',
                                            'db_len' => 4,
                                            'db_isNull' => 0,
                                            'db_unsigned'=>true,
                                   			'db_default'=>0
                                   )   
        )
);

