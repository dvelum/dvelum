<?php
/**
 * Operations with CRUD window components
 */
use Dvelum\Orm;
class Backend_Designer_Sub_Crudwindow extends Backend_Designer_Sub
{
	/**
	 * @var Designer_Project
	 */
	protected $_project;
	/**
	 * @var Ext_Property_Component_Window_System_Crud
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
	 * Import fields into the form object
	 */
	public function importfieldsAction()
	{
		$importObject = Request::post('importobject', 'string', false);
		$importFields = Request::post('importfields', 'array', array());
		
		if(!$importObject || empty($importFields)  ||  !Orm\Record\Config::configExists($importObject))
			Response::jsonError($this->_lang->WRONG_REQUEST);
		
		$importObjectConfig = Orm\Record\Config::factory($importObject);
		
		foreach ($importFields as $name)
			if($importObjectConfig->fieldExists($name))
				$this->_importOrmField($name, $importObjectConfig);
			
		$this->_object->objectName = $importObject;		
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
		$tabName = $this->_object->getName().'_generalTab';
				
		if(!$this->_project->objectExists($tabName))
		{
			$tab = Ext_Factory::object('Panel');
			$tab->setName($tabName);
			$tab->frame = false;
			$tab->border = false;
			$tab->layout = 'anchor';
			$tab->bodyPadding = 3;
			$tab->bodyCls = 'formBody';
			$tab->anchor = '100%';
			$tab->scrollable = true;
			$tab->title = Lang::lang()->GENERAL;
			$tab->fieldDefaults = "{
			            labelAlign: 'right',
			            labelWidth: 160,
			            anchor: '100%'
			     }";
			
			$this->_project->addObject($this->_object->getName(), $tab);
		}

		$tabsArray = array('Component_Field_System_Medialibhtml' , 'Component_Field_System_Related', 'Component_Field_System_Objectslist');
		
		$newField = Backend_Designer_Import::convertOrmFieldToExtField($name , $importObjectConfig->getFieldConfig($name));	
		
		if($newField!==false)
		{
		    $fieldClass = $newField->getClass();
		    if($fieldClass == 'Component_Field_System_Objectslist' || $fieldClass == 'Component_Field_System_Objectlink')
		        $newField->controllerUrl = $this->_object->controllerUrl;
		    
			$newField->setName($this->_object->getName().'_'.$name);

			if(in_array($fieldClass , $tabsArray , true))
			    $this->_project->addObject($this->_object->getName(), $newField);
			else
			    $this->_project->addObject($tabName, $newField);
			
		}
					
	}
}