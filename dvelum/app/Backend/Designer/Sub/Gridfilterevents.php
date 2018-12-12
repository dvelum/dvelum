<?php
class Backend_Designer_Sub_Gridfilterevents extends Backend_Designer_Sub
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
	 * @var Ext_Grid_Filter
	 */
	protected $_filter;
	
	public function __construct()
	{
		parent::__construct();
		$this->_checkLoaded();
		$this->_checkObject();
		$this->_checkFilter();
	}
	
	public function _checkFilter()
	{
	  $object = $this->_object;
	  $filter = Request::post('id','string',false);
	  
	  if($filter === false || $object->getClass()!=='Grid' || !$object->getFiltersFeature()->filterExists($filter))
	      Response::jsonError('Cant find filter');
	  
	  $filterObject = $object->getFiltersFeature()->getFilter($filter);
	  
	  if(!$filterObject instanceof Ext_Grid_Filter)
	      Response::jsonError('Invalid filter type');
	  
	  $this->_filter = $filterObject;
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
	    $objectName = $this->_filter->getName();
	
	    $objectEvents = $this->_project->getEventManager()->getObjectEvents($objectName);
	
	    $events = $this->_filter->getConfig()->getEvents();
	
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
	
	protected function _getEvent()
	{
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
	
	    if($eventManager->eventExists($this->_filter->getName(), $event))
	        $code = $eventManager->getEventCode($this->_filter->getName(), $event);
	    else
	        $code = '';
	
	    Response::jsonSuccess(array('code'=>$code));
	}
	
	public function saveeventAction()
	{
	    $project = $this->_getProject();
	
	    $event = $this->_getEvent();
	    $code = Request::post('code', 'raw', '');
        $buffer = Request::post('buffer', Filter::FILTER_INTEGER, false);
        if(empty($buffer)){
            $buffer = false;
        }
	    $events = $this->_filter->getConfig()->getEvents();
	    

	    $project->getEventManager()->setEvent($this->_filter->getName(), $event, $code , $events->$event, false, $buffer);
	    $this->_storeProject();
	    Response::jsonSuccess();
	}
	
	
	public function removeeventAction(){
	    $project = $this->_getProject();
	    $event = $this->_getEvent();
	    $project->getEventManager()->removeObjectEvent($this->_filter->getName() , $event);
	    $this->_storeProject();
	    Response::jsonSuccess();
	}
}