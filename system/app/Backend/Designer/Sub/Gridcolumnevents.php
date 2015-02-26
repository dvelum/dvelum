<?php
class Backend_Designer_Sub_Gridcolumnevents extends Backend_Designer_Sub
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
		$this->_checkColumn();
		$this->_checkAction();
	}
	
	protected  function _checkAction()
	{
		$name = Request::post('id', 'string', '');
		
		if(!$this->_column->actionExists($name))
			Response::jsonError($this->_lang->WRONG_REQUEST .' invalid action');
		
		$this->_action = $this->_column->getAction($name);
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


	protected function _convertParams($config)
	{
		if(empty($config))
			return '';
		
		foreach ($config as $pName=>$pType)
			$paramsArray[] = '<span style="color:green;">' . $pType . '</span> ' . $pName;
		
		return implode(' , ', $paramsArray);
		
	}
	/**
	 * Get events for object
	 */
	public function objecteventsAction()
	{
		$objectName = $this->_action->getName();
		
		$objectEvents = $this->_project->getEventManager()->getObjectEvents($objectName);	
		
		$events = $this->_action->getConfig()->getEvents();

		$result = array();
		$id =1;
		foreach ($events as $name=>$config)
		{
			if(isset($objectEvents[$name]) && !empty($objectEvents[$name]))
				$hasCode = true;
			else 
				$hasCode = false;
					
			$result[] = array(
				'id'=>$id,
				'object'=>$objectName,
				'event'=>$name,
				'params'=>$this->_convertParams($config),
				'has_code'=>$hasCode
			);
			$id++;
		}

		Response::jsonSuccess($result);
	}
	
	protected function _getEvent(){
		$event = Request::post('event', 'string', false);
		if(!strlen($event) || $event === false)
			Response::jsonError($this->_lang->WRONG_REQUEST);
		return $event;
	}
	
	public function eventcodeAction()
	{
		$project = $this->_getProject();
		$event = $this->_getEvent();
		
		$eventManager = $project->getEventManager();
		
		if($eventManager->eventExists($this->_action->getName(), $event))
			$code = $eventManager->getEventCode($this->_action->getName(), $event);
		else
			$code = '';	
		
		Response::jsonSuccess(array('code'=>$code));
	}
	
	public function saveeventAction()
	{
		$project = $this->_getProject();

		$event = $this->_getEvent();
		$code = Request::post('code', 'raw', '');
		$events = $this->_action->getConfig()->getEvents();
		
		$project->getEventManager()->setEvent($this->_action->getName(), $event, $code , $events->$event);
		$this->_storeProject();
		Response::jsonSuccess();		
	}

	public function removeeventAction(){
		$project = $this->_getProject();		
		$event = $this->_getEvent();
		$eventManager = $project->getEventManager()->removeObjectEvent($this->_action->getName() , $event);
		$this->_storeProject();
		Response::jsonSuccess();
	}
	
}