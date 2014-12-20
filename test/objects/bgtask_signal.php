<?php return array (
  'table' => 'bgtask_signal',
  'engine' => 'Memory',
  'rev_control' => false,
  'save_history' => false,
  'link_title' => '',
  'system'=>true,
  'fields' => 
  array (
    'pid' => 
    array (
      'type' => 'link',
      'title' => 'Task PID',
      'unique' => '',
      'required' => true,
      'link_config' => 
      array (
        'link_type' => 'object',
        'object' => 'bgtask',
      ),
	   'db_type' => 'bigint',
	   'db_isNull' => 0,
	   'db_default' => 0,
	   'db_unsigned' => true,
    ),
    'signal' => 
    array (
      'type' => '',
      'title' => 'Signal',
      'unique' => '',
      'db_isNull' => false,
      'required' => true,
      'db_type' => 'bigint',
      'db_default' => 0,
      'db_unsigned' => true,
    ),
  ),
); 