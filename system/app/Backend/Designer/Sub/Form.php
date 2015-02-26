<?php
/**
 * Operations with forms
 */
class Backend_Designer_Sub_Form extends Backend_Designer_Sub
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
		if(!strlen($name) || !$project->objectExists($name) || $project->getObject($name)->getClass()!=='Form')
			Response::jsonError($this->_lang->WRONG_REQUEST);
	
		$this->_project = $project;
		$this->_object = $project->getObject($name);
	}
	
	/**
	 * Import fields into the form object
	 */
	public function importfieldsAction()
	{
		$importObject = Request::post('importobject', 'string', false);
		$importFields = Request::post('importfields', 'array', array());
		
		if(!$importObject || empty($importFields)  || $this->_project->objectExists($importObject))
			Response::jsonError($this->_lang->WRONG_REQUEST);
		
		$importObjectConfig = Db_Object_Config::getInstance($importObject);
		
		foreach ($importFields as $name)
			if($importObjectConfig->fieldExists($name))
				$this->_importOrmField($name, $importObjectConfig);
			
		$this->_storeProject();
		Response::jsonSuccess();		
	}
	
	/**
	 * Import DB fields into the form object
	 */
	public function importdbfieldsAction()
	{
		$connection = Request::post('connection', 'string', false);
		$table = Request::post('table', 'string', false);
		$conType = Request::post('type', 'integer', false);
		
		$importFields = Request::post('importfields', 'array', array());
		
		if($connection === false || !$table || empty($importFields) || $conType===false)
			Response::jsonError($this->_lang->WRONG_REQUEST);

		$conManager = new Backend_Orm_Connections_Manager($this->_configMain->get('db_configs'));
		$cfg = $conManager->getConnection($conType, $connection);
		if(!$cfg)
		    Response::jsonError($this->_lang->WRONG_REQUEST);
		$cfg = $cfg->__toArray();
	
		$tableFields = Backend_Designer_Import::getTableFields($cfg, $table);
		
		if($tableFields === false)
			Response::jsonError($this->_lang->CANT_CONNECT);
		
		foreach ($importFields as $name)
			if(isset($tableFields[$name]) && !empty($tableFields[$name]))
				$this->_importDbField($name , $tableFields[$name]);

		$this->_storeProject();
		Response::jsonSuccess();		
	}
	/**
	 * Conver field from ORM format and add to the project
	 * @param string $name
	 * @param Db_Object_Config $importObject
	 */
	protected function _importOrmField($name , $importObjectConfig)
	{
		$newField = Backend_Designer_Import::convertOrmFieldToExtField($name , $importObjectConfig->getFieldConfig($name));	
		if($newField!==false)
		{
			$newField->setName($this->_object->getName().'_'.$name);
			$this->_project->addObject($this->_object->getName(), $newField);
		}
					
	}
	/**
	 * Conver DB column into Ext field
	 * @param string $name
	 * @param array $config
	 */
	protected function _importDbField($name , $config)
	{						
		$newField = Backend_Designer_Import::convertDbFieldToExtField($config);		
		if($newField!==false)
		{
			$newField->setName($this->_object->getName().'_'.$name);
			$this->_project->addObject($this->_object->getName(), $newField);	
		}	
	}
}