<?php
class Backend_Designer_Sub_Gridcolumnactions extends Backend_Designer_Sub
{
	/**
	 * @var Designer_Project
	 */
	protected $_project;
	/**
	 * @var Ext_Grid
	 */
	protected $_object;
	/**
	 * @var Ext_Grid_Column_Action
	 */
	protected $_column;

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

	protected function _checkColumn()
	{
		$object = $this->_object;
		$column = Request::post('column','string',false);

		if($column === false || $object->getClass()!=='Grid' || !$object->columnExists($column))
			Response::jsonError('Cant find column');

		$columnObject = $object->getColumn($column);

		if($columnObject->getClass()!=='Grid_Column_Action')
			Response::jsonError('Invalid column type');

		$this->_column = $columnObject;
	}

	/**
	 * Get object properties
	 */
	public function listAction()
	{
		$this->_checkColumn();

		$action = Request::post('id','string',false);

		if($action === false || !$this->_column->actionExists($action))
			Response::jsonError($this->_lang->WRONG_REQUEST .' invalid action');

		$action = $this->_column->getAction($action);
		$data = $action->getConfig()->__toArray();
		unset($data['handler']);
		Response::jsonSuccess($data);
	}
	/**
	 * Set object property
	 */
	public function setpropertyAction()
	{
		$this->_checkColumn();

		$action = Request::post('id','string',false);

		if($action === false || !$this->_column->actionExists($action))
			Response::jsonError($this->_lang->WRONG_REQUEST .' invalid action');

		$action = $this->_column->getAction($action);

		$property = Request::post('name', 'string', false);
		$value = Request::post('value', 'raw', false);


		if(!$action->isValidProperty($property))
			Response::jsonError();

		$action->$property = $value;
		$this->_storeProject();
		Response::jsonSuccess();
	}
	/**
	 * Get column renderers CAP
	 * @todo remove request from interface
	 */
	public function renderersAction()
	{
		Response::jsonSuccess(array());
	}
}