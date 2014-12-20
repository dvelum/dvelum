<?php
class Backend_Designer_Sub_Gridcolumn extends Backend_Designer_Sub
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
	 * Get columns list as tree structure
	 */
	public function columnlistAction()
	{
		Response::jsonArray($this->_object->getColumsList());
	}
	/**
	 * Get object properties
	 */
	public function listAction()
	{
		$id = Request::post('id', 'string', false);

		if(!$id || !$this->_object->columnExists($id))
			Response::jsonError($this->_lang->WRONG_REQUEST);

		$data = array();

		$column = $this->_object->getColumn($id);
		$config = $column->getConfig();
		$properties = $config->__toArray();

		if($config->xtype !== 'actioncolumn'){
			unset($properties['items']);
		}else{
			unset($properties['renderer']);
			unset($properties['summaryRenderer']);
			unset($properties['summaryType']);
		}
		Response::jsonSuccess($properties);
	}
	/**
	 * Set object property
	 */
	public function setpropertyAction()
	{
		$id = Request::post('id', 'string', false);
		$property = Request::post('name', 'string', false);
		$value = Request::post('value', 'string', false);

		if(!$id || !$this->_object->columnExists($id))
			Response::jsonError($this->_lang->WRONG_REQUEST);

		$object = $this->_object->getColumn($id);
		if(!$object->isValidProperty($property))
			Response::jsonError();

		if($property === 'text'){
			$value = Request::post('value', 'raw', false);
		}

		$object->$property = $value;
		$this->_storeProject();
		Response::jsonSuccess();
	}
	/**
	 * Get list of available renderers
	 */
	public function renderersAction()
	{
		$data = array();
		$autoloaderPaths = $this->_configMain['autoloader'];
		$autoloaderPaths = $autoloaderPaths['paths'];
		$files = File::scanFiles($this->_config->get('components').'/Renderer',array('.php'),true,File::Files_Only);
		$data[] = array('id'=>'' , 'title'=>$this->_lang->NO);

		/**
		 * This is hard fix for windows
		 */
		if(DIRECTORY_SEPARATOR == '\\')
		{
			foreach ($files as &$v)
			{
				$v = str_replace('\\', '/', $v);
				$v = str_replace('//', '/', $v);
			}
			unset($v);
		}

		if(!empty($files))
		{
			foreach ($files as $item)
			{
				$class = Utils::classFromPath(str_replace($autoloaderPaths, '', $item));
				$data[] = array('id'=>$class , 'title'=>str_replace($this->_config->get('components').'/Renderer/', '', substr($item,0,-4)));
			}
		}
		Response::jsonArray($data);
	}
	/**
	 * Change column width
	 */
	public function changesizeAction()
	{
		$object = Request::post('object', 'string', false);
		$column = Request::post('column', 'string', false);
		$width = Request::post('width', 'integer', false);

		$project = $this->_getProject();

		if($object===false || !$project->objectExists($object) || $column===false || $width===false)
			Response::jsonError($this->_lang->WRONG_REQUEST );

		$object = $project->getObject($object);

		if($object->getClass()!=='Grid' || !$object->columnExists($column))
			Response::jsonError($this->_lang->WRONG_REQUEST);

		$column = $object->getColumn($column);
		$column->width = $width;

		$this->_storeProject();

		Response::jsonSuccess();
	}
	/**
	 * Move column
	 */
	public function moveAction()
	{
		$object = Request::post('object', 'string', false);
		$column = Request::post('column', 'string', false);
		$from = Request::post('from', 'integer', false);
		$to = Request::post('to', 'integer', false);

		$project = $this->_getProject();

		if($object===false || !$project->objectExists($object) || $column===false || $from===false || $to===false)
			Response::jsonError($this->_lang->WRONG_REQUEST);

		$object = $project->getObject($object);

		if($object->getClass()!=='Grid' || !$object->columnExists($column))
			Response::jsonError($this->_lang->WRONG_REQUEST);

		$object->moveColumn($column , $from , $to);

		$this->_storeProject();

		Response::jsonSuccess();
	}
	/**
	 * Get list of items for actioncolumn
	 */
	public function itemslistAction()
	{
	    $designerManager = new Designer_Manager($this->_configMain);

		$object = $this->_object;
		$column = Request::post('column','string',false);

		if($column === false)
			Response::jsonErrot($this->_lang->WRONG_REQUEST . ' code 1');

		if($object->getClass()!=='Grid' || !$object->columnExists($column))
			Response::jsonError($this->_lang->WRONG_REQUEST  . ' code 2');

		$columnObject = $object->getColumn($column);

		if($columnObject->getClass()!=='Grid_Column_Action')
			Response::jsonError($this->_lang->WRONG_REQUEST  . ' code 3');

		$result = array();
		$actions = $columnObject->getActions();
		if(!empty($actions))
		{
			foreach ($actions as $name=>$object)
			{
				$result[] = array(
								'id'=>$name,
								'icon'=>Designer_Factory::replaceCodeTemplates($designerManager->getReplaceConfig(), $object->icon),
								'tooltip'=>$object->tooltip
							);
			}
		}
		Response::jsonSuccess($result);
	}

	public function addactionAction()
	{
		$object = $this->_object;
		$actionName = Request::post('name','alphanum',false);
		$column = Request::post('column','string',false);

		if($actionName === false || $column === false)
			Response::jsonErrot($this->_lang->WRONG_REQUEST . ' code 1');

		if($object->getClass()!=='Grid' || !$object->columnExists($column))
			Response::jsonError($this->_lang->WRONG_REQUEST  . ' code 2');

		$columnObject = $object->getColumn($column);

		if($columnObject->getClass()!=='Grid_Column_Action')
			Response::jsonError($this->_lang->WRONG_REQUEST  . ' code 3');

		$actionName = $this->_object->getName().'_action_'.$actionName;

		if($columnObject->actionExists($actionName))
			Response::jsonError($this->_lang->SB_UNIQUE);

		$newButton = Ext_Factory::object('Grid_Column_Action_Button',array('text'=>$actionName));
		$newButton->setName($actionName);

		$columnObject->addAction($actionName , $newButton);
		$this->_storeProject();
		Response::jsonSuccess();
	}

	public function removeactionAction()
	{
		$object = $this->_object;
		$actionName = Request::post('name','alphanum',false);
		$column = Request::post('column','string',false);

		if($actionName === false || $column === false)
			Response::jsonErrot($this->_lang->WRONG_REQUEST . ' code 1');

		if($object->getClass()!=='Grid' || !$object->columnExists($column))
			Response::jsonError($this->_lang->WRONG_REQUEST  . ' code 2');

		$columnObject = $object->getColumn($column);

		if($columnObject->getClass()!=='Grid_Column_Action')
			Response::jsonError($this->_lang->WRONG_REQUEST  . ' code 3');


		$columnObject->removeAction($actionName);

		$this->_project->getEventManager()->removeObjectEvents($actionName);

		$this->_storeProject();
		Response::jsonSuccess();
	}

	public function sortactionsAction()
	{
		$object = $this->_object;
		$order = Request::post('order','array',array());
		$column = Request::post('column','string',false);

		if($column === false)
			Response::jsonErrot($this->_lang->WRONG_REQUEST . ' code 1');

		if($object->getClass()!=='Grid' || !$object->columnExists($column))
			Response::jsonError($this->_lang->WRONG_REQUEST  . ' code 2');

		$columnObject = $object->getColumn($column);

		if($columnObject->getClass()!=='Grid_Column_Action')
			Response::jsonError($this->_lang->WRONG_REQUEST  . ' code 3');

		if(!empty($order))
		{
			$index = 0;
			foreach ($order as $name)
			{
				if($columnObject->actionExists($name)){
					$columnObject->setActionOrder($name , $index);
					$index++;
				}
			}
			if($index>0)
				$columnObject->sortActions();
		}

		$this->_storeProject();
		Response::jsonSuccess();
	}
}