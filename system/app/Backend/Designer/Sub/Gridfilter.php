<?php
class Backend_Designer_Sub_Gridfilter extends Backend_Designer_Sub
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
	    $id = Request::post('id', 'string', false);
	
	    if(!$id || !$this->_object->getFiltersFeature()->filterExists($id))
	        Response::jsonError($this->_lang->WRONG_REQUEST);
	
	    $filter = $this->_object->getFiltersFeature()->getFilter($id);
	    $config = $filter->getConfig();
	    $properties = $config->__toArray();
	    
	    $unset = array('isExtended','listeners','type');
	    foreach ($unset as $property)
	      if(isset($properties[$property]))
	          unset($properties[$property]);
	    
	    Response::jsonSuccess($properties);
	}
		
	/**
	 * Set object property
	 */
	public function setpropertyAction()
	{
	    $id = Request::post('id', 'string', false);
	    $property = Request::post('name', 'string', false);
	    $value = Request::post('value', 'raw', false);
	
	    if(!$id || !$this->_object->getFiltersFeature()->filterExists($id))
	        Response::jsonError($this->_lang->WRONG_REQUEST);
	
	    $object = $this->_object->getFiltersFeature()->getFilter($id);
	    if(!$object->isValidProperty($property))
	        Response::jsonError();
	
	    $object->$property = $value;
	    $this->_storeProject();
	    Response::jsonSuccess();
	}
}