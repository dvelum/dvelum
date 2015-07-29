<?php
return array(
    'config'=>array(
        // 5m
        'time_limit'=> 300,
        // 5m
        'intercept_timeout'=>300,
        'locks_dir'=> './temp/locks/',
        'log_file' => './logs/cronjobs.log',
    	'user_id'=>1
    ),
    //============= Tasks =============
    'clearmemory' =>array(
      'adapter' => 'Cronjob_Clearmemory'
    ),
    'sometask'=>array(
      'property_1' => 10,
      'property_2' => 100,
      'adapter' => 'Task_Cronjob_Test'
    ),
    'somejob'=>array(
	   'adapter' => 'Cronjob_Test'
    )
);