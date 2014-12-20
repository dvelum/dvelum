<?php
class Backend_Logs_Controller extends Backend_Controller
{
	/**
	 * (non-PHPdoc)
	 * @see Backend_Controller::indexAction()
	 */
	public function indexAction()
	{      	
        $res = Resource::getInstance();    
	    $res->addJs('/js/app/system/crud/logs.js' , true , 1);
	    
	    $actions = array(array('id'=>0,'title'=>$this->_lang->ALL));
	    
	    foreach (Model_Historylog::$actions as $k=>$v)
	    	$actions[] = array('id'=>$k,'title'=>$v);
	    
	    $res->addInlineJs('var logActions = '.json_encode($actions).';');
    }
    
    public function listAction()
    {
    	$logsModel = Model::factory('Historylog');
    	$pager = Request::post('pager', 'array', array());
    	$filter = Request::post('filter', 'array', array());
    	
    	if(isset($filter['type']) && $filter['type'] == 0)
    		unset($filter['type']);
    	
    	$count = $logsModel->getCount($filter);
    	
    	$joins = array(array(
	 			'joinType'=>'joinLeft',
	  			'table' => array('u'=>Model::factory('User')->table()),
	 			'fields' => array('user'=>'name'),
	  			'condition'=> 'u.id = user_id'
	  	));
    	
    	$list = $logsModel->getListVc(
    		$pager, 
    		$filter, 
    		false ,
    		'*',
    		false,
    		false,
    		$joins
    	);
    	
    	if(!empty($list))
    		foreach ($list as &$value)
    	        if(isset(Model_Historylog::$actions[$value['type']]))
    			   $value['type'] = Model_Historylog::$actions[$value['type']];
    	   	
    	Response::jsonSuccess($list,array('count'=>$count));
    }
}