<?php
class Frontend_Api_Controller extends Frontend_Controller
{

	public function __construct()
	{
		parent::__construct();
		
		$key = Request::post('key', 'alphanum', false);
		if($key === false)
			Response::jsonError($this->_lang->WRONG_REQUEST);
					
		$apikeysModel = Model::factory('apikeys');
		
		if(strlen($key) == 32)
		    $data = $apikeysModel->getItemByUniqueField('hash', $key);	
		else
		    $data = $apikeysModel->getItemByUniqueField('hash', Utils::hash($key));	
		
		if(empty($data) || !$data['active'])				
			Response::jsonError($this->_lang->ACCESS_DENIED);	
	}
	
	public function indexAction()
	{
		$controller = Filter::filterValue('pagecode',Request::getInstance()->getPart(1));
		$action = Filter::filterValue('pagecode',Request::getInstance()->getPart(2));
		
		if(!strlen($controller) || !strlen($action))
			Response::jsonError($this->_lang->WRONG_REQUEST.' c1');
		
		$apiController = 'Api_' . ucfirst($controller);
		$apiAction = $action . 'Action';

		if(!class_exists($apiController))
			Response::jsonError($this->_lang->WRONG_REQUEST.' c2');
			
		$controller = new $apiController(Registry::get('main','config') , $this->_db);

		if(!method_exists($controller, $apiAction))
			Response::jsonError($this->_lang->WRONG_REQUEST.' c3 '.$apiAction );		
		
		$controller->$apiAction();
	}
}