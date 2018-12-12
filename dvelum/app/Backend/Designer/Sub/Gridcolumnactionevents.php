<?php
/**
 * Events for action column, migrated from DVelum 0.9.x
 */
class Backend_Designer_Sub_Gridcolumnactionevents extends Backend_Designer_Sub_Column_Events
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
     * @var string|null
     */
    protected $_action = null;

    public function __construct()
    {
        parent::__construct();
        $this->_checkAction();
    }

    protected  function _checkAction()
    {
        $name = Request::post('id', 'string', '');

        if(!$this->_column->actionExists($name))
            Response::jsonError($this->_lang->WRONG_REQUEST .' invalid action');

        $this->_action = $this->_column->getAction($name);
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
        $buffer = Request::post('buffer', Filter::FILTER_INTEGER, false);
        if(empty($buffer)){
            $buffer = false;
        }
        $events = $this->_action->getConfig()->getEvents();

        $project->getEventManager()->setEvent($this->_action->getName(), $event, $code , $events->$event, false, $buffer);
        $this->_storeProject();
        Response::jsonSuccess();
    }

    public function removeeventAction(){
        $project = $this->_getProject();
        $event = $this->_getEvent();
        $project->getEventManager()->removeObjectEvent($this->_action->getName() , $event);
        $this->_storeProject();
        Response::jsonSuccess();
    }

}