<?php
class Backend_Designer_Sub_Datafield extends Backend_Designer_Sub
{
	/**
	 * @var Designer_Project
	 */
	protected $_project;
	/**
	 * @var Ext_Data_Store
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
		
		if(!strlen($name) || !$project->objectExists($name))
			Response::jsonError($this->_lang->WRONG_REQUEST);
	
		$this->_project = $project;
		$this->_object = $project->getObject($name);
	}
	
	/**
	 * Set object property
	 */
	public function setpropertyAction()
	{
	    $id = Request::post('id', 'string', false);
	    $property = Request::post('name', 'string', false);
	    $value = Request::post('value', 'string', false);
	    
	    if(!$id || !$this->_object->fieldExists($id))
	        Response::jsonError($this->_lang->WRONG_REQUEST);
	    
	    $field = $this->_object->getField($id);
	    
	    if(!$field->isValidProperty($property))
	        Response::jsonError();
	    
	    if($property === 'name' && !$this->_object->renameField($field->name , $value))	       
	        Response::jsonError();
	    
	   	$field->$property = $value;
	   	$this->_storeProject();
	    Response::jsonSuccess();    
	}
	
	/**
	 * Get object properties
	 */
	public function listAction()
	{
	    $id = Request::post('id', 'string', false);
	
	    if(!method_exists($this->_object, 'fieldExists')){
	        Response::jsonError(get_class($this->_object) .'['.$this->_object->getName().'] deprecated type');
	    }
	    
	    if(!$id || !$this->_object->fieldExists($id))
	        Response::jsonError($this->_lang->WRONG_REQUEST);

	    $field = $this->_object->getField($id);
	    $config = $field->getConfig();
	    $properties = $config->__toArray();
	    
	    if(isset($properties['isExtended']))
	        unset($properties['isExtended']);
	    	
	    Response::jsonSuccess($properties);
	}
}