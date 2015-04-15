<?php
class Backend_Designer_Sub_Gridfilters extends Backend_Designer_Sub
{
	/**
	 * @var Designer_Project
	 */
	protected $_project;
	/**
	 * @var Ext_Grid
	 */
	protected $_object;


	public function __construct()
	{
		parent::__construct();
		$this->_checkLoaded();
		$this->_checkObject();
	}
	
	protected function _checkObject()
	{	
		$name = Request::post('object', 'string', '');
		$project = $this->_getProject();
		
		if(!strlen($name) || !$project->objectExists($name) || $project->getObject($name)->getClass()!=='Grid')
			Response::jsonError($this->_lang->WRONG_REQUEST);
	
		$this->_project = $project;
		$this->_object = $project->getObject($name);
	}
	
	
    /**
	 * Get object properties
	 */
	public function listAction()
	{
		$filters = $this->_object->getFiltersFeature();
		$config = $filters->getConfig();
		$properties = $config->__toArray();
		
		if(isset($properties['filters']))
		  unset($properties['filters']);
		
		Response::jsonSuccess($properties);
	}
	/**
	 * Set object property
	 */
	public function setpropertyAction()
	{
		$property = Request::post('name', 'string', false);
		$value = Request::post('value', 'raw', false);
		$object = $this->_object->getFiltersFeature();	
		$object->$property = $value;
		$this->_storeProject();
		Response::jsonSuccess();
	}
	
	/**
	 * Get filters list
	 */
	public function filterlistAction()
	{
	  $filters = $this->_object->getFiltersFeature()->getFilters();
	 
	  $data = array();
	  
	  if(!empty($filters))
	    foreach($filters as $k=>$v)
	       $data[] = array('id'=>$k , 'dataIndex'=>$v->dataIndex , 'active' =>$v->active,'type'=>$v->getType());
	  
	  Response::jsonSuccess($data);
	}
	/**
	 * Add filter
	 */
	public function addfilterAction()
	{
	  $filterId = Request::post('id','pagecode', '');
	  
	  if(!strlen($filterId))
	      Response::jsonError($this->_lang->INVALID_VALUE);
	  
	  if($this->_object->getFiltersFeature()->filterExists($filterId))
	      Response::jsonError($this->_lang->SB_UNIQUE);
	  
	  $filter = Ext_Factory::object('Grid_Filter_String');
	  $filter->setName($this->_object->getName().'_filter_'.$filterId);
	  $filter->active=true;
	  
	  if(!$this->_object->getFiltersFeature()->addFilter($filterId , $filter))
	      Response::jsonError($this->_lang->INVALID_VALUE);
	  	
	  $this->_storeProject();
	  Response::jsonSuccess();
	}
	/**
	 * Remove grid column
	 */
	public function removefilterAction(){
	    $fId = Request::post('id','pagecode', '');
	    if(!strlen($fId))
	        Response::jsonError($this->_lang->INVALID_VALUE . 'code 1');
	
	    if(!$this->_object->getFiltersFeature()->filterExists($fId))
	        Response::jsonError($this->_lang->INVALID_VALUE . 'code 2');
	    	
	    $this->_object->getFiltersFeature()->removeFilter($fId);
	    $this->_storeProject();
	    Response::jsonSuccess();
	}
	/**
	 * Change grid filter type
	 */
	public function changefiltertypeAction()
	{
	    $type = Request::post('type', 'string', '');
	    $filterId = Request::post('filterid','pagecode',false);
	
	    if(!$filterId)
	        Response::jsonError($this->_lang->WRONG_REQUEST);
	
	    if(strlen($type))
	        $name = 'Grid_Filter_'.ucfirst($type);
	    else
	        $name = 'Grid_Filter_String';
	
	    $oldFilter = $this->_object->getFiltersFeature()->getFilter($filterId);
	    $newFilter = Ext_Factory::object($name);
	
	    Ext_Factory::copyProperties($oldFilter, $newFilter);
	    $newFilter->setName($oldFilter->getName());
	
	    
	    switch ($type)
	    {
	      case 'date':
	        if(empty($newFilter->dateFormat))
	          $newFilter->dateFormat = "Y-m-d";
	        
	        if(empty($newFilter->afterText))
	            $newFilter->afterText = '[js:] appLang.FILTER_AFTER_TEXT';
	         
	        if(empty($newFilter->beforeText))
	            $newFilter->beforeText ='[js:] appLang.FILTER_BEFORE_TEXT';
	         
	        if(empty($newFilter->onText))
	            $newFilter->onText = '[js:] appLang.FILTER_ON_TEXT';
	        
	        break;
	        
	      case 'datetime' :
	        if(empty($newFilter->dateFormat))
	            $newFilter->dateFormat = "Y-m-d";
	         
	        $newFilter->date='{format: "Y-m-d"}';
	        $newFilter->time='{format: "H:i:s",increment:1}';
	        
	        if(empty($newFilter->afterText))
	         $newFilter->afterText = '[js:] appLang.FILTER_AFTER_TEXT';
	        
	        if(empty($newFilter->beforeText))
	         $newFilter->beforeText ='[js:] appLang.FILTER_BEFORE_TEXT';
	        
	        if(empty($newFilter->onText))
	         $newFilter->onText = '[js:] appLang.FILTER_ON_TEXT';
	        
	        break;
	        
	      case 'list':
	        $newFilter->phpMode = true;
	        break;
	        
	      case 'boolean':
	        $newFilter->noText = '[js:] appLang.NO';
	        $newFilter->yesText = '[js:] appLang.YES';
	        break;
	    }	    

	    if(!$this->_object->getFiltersFeature()->setFilter($filterId, $newFilter))
	        Response::jsonError($this->_lang->WRONG_REQUEST);
	    	
	    $this->_storeProject();
	    Response::jsonSuccess();
	}
}