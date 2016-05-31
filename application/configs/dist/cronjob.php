<?php
return array(
    'config'=>array(
        // 5m
        'time_limit'=> 300,
        // 5m
        'intercept_timeout'=>300,
        'locks_dir'=> './data/locks/',
        'log_file' => './data/logs/cronjobs.log',
    	'user_id'=>1
    ),
    //============= Tasks =============
    'testTask'=>array(
      'property_1' => 10,
      'property_2' => 100,
      'adapter' => 'Task_Cronjob_Test'
    ),
    //============ Jobs ===============
    'testJob'=>array(
	   'adapter' => 'Cronjob_Test'
    )
);