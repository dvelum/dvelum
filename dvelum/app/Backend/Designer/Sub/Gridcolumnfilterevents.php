<?php
class Backend_Designer_Sub_Gridcolumnfilterevents extends Backend_Designer_Sub_Column_Events
{
    /**
     * @var Ext_Grid_Filter
     */
    protected $_filter;

    public function __construct()
    {
        parent::__construct();
        $this->_checkFilter();
    }

    /**
     * Check if column has filter
     */
    protected  function _checkFilter()
    {
        $filter = $this->_column->filter;

        if(empty($filter) || !$filter instanceof Ext_Grid_Filter)
            Response::jsonError($this->_lang->WRONG_REQUEST .' invalid filter');

        $this->_filter = $filter;
    }

    protected function _checkColumn()
    {
        $object = $this->_object;
        $column = Request::post('column','string',false);

        if($column === false || $object->getClass()!=='Grid' || !$object->columnExists($column))
            Response::jsonError('Cant find column');

        $columnObject = $object->getColumn($column);

        $this->_column = $columnObject;
    }

    /**
     * Generate event object name
     */
    protected function _getEventObject()
    {
        return $this->_object->getName().'.filter.'.$this->_filter->getName();
    }

    /**
     * Get events for object
     */
    public function objecteventsAction()
    {
        $eObject = $this->_getEventObject();

        $objectEvents = $this->_project->getEventManager()->getObjectEvents($eObject);

        $events = $this->_filter->getConfig()->getEvents();

        $result = [];
        $id =1;
        foreach ($events as $name=>$config)
        {
            if(isset($objectEvents[$name]) && !empty($objectEvents[$name]))
                $hasCode = true;
            else
                $hasCode = false;

            $result[] = array(
                'id'=>$id,
                'object'=>$eObject,
                'event'=>$name,
                'params'=>$this->_convertParams($config),
                'has_code'=>$hasCode
            );
            $id++;
        }
        Response::jsonSuccess($result);
    }

    /**
     * Get action code for event
     */
    public function eventcodeAction()
    {
        $project = $this->_getProject();
        $event = $this->_getEvent();
        $eventManager = $project->getEventManager();
        $eObject = $this->_getEventObject();

        if($eventManager->eventExists($eObject, $event))
            $code = $eventManager->getEventCode($eObject, $event);
        else
            $code = '';

        Response::jsonSuccess(array('code'=>$code));
    }

    /**
     * Save event handler
     */
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
        $eObject = $this->_getEventObject();

        $project->getEventManager()->setEvent($eObject, $event, $code , $events->$event, false, $buffer);
        $this->_storeProject();
        Response::jsonSuccess();
    }

    /**
     * Remove event handler
     */
    public function removeeventAction()
    {
        $project = $this->_getProject();
        $event = $this->_getEvent();
        $eObject = $this->_getEventObject();
        $project->getEventManager()->removeObjectEvent($eObject, $event);
        $this->_storeProject();
        Response::jsonSuccess();
    }
}