<?php

/**
 * Events for grid columns
 */
class Backend_Designer_Sub_Gridcolumnevents extends Backend_Designer_Sub_Column_Events
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

    /**
     * Get events for object
     */
    public function objecteventsAction()
    {
        $eObject = $this->_getEventObject();
        $objectEvents = $this->_project->getEventManager()->getObjectEvents($eObject);

        $events = $this->_column->getConfig()->getEvents();

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
     * Get event code (JS code)
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
     * Generate event object name
     */
    protected function _getEventObject()
    {
        return $this->_object->getName().'.column.'.$this->_column->getName();
    }

    /**
     * Update column event
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
        $events = $this->_column->getConfig()->getEvents();

        $eObject = $this->_getEventObject();

        $project->getEventManager()->setEvent($eObject, $event, $code , $events->$event, false, $buffer);
        $this->_storeProject();
        Response::jsonSuccess();
    }

    /**
     * Remove column event
     */
    public function removeeventAction()
    {
        $project = $this->_getProject();
        $event = $this->_getEvent();
        $eObject = $this->_getEventObject();
        $project->getEventManager()->removeObjectEvent($eObject , $event);
        $this->_storeProject();
        Response::jsonSuccess();
    }
}