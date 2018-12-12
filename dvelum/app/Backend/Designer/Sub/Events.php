<?php
class Backend_Designer_Sub_Events extends Backend_Designer_Sub
{
    /**
     * Get all registered events
     */
    public function listAction()
    {
        $project = $this->_getProject();
        $eventManager = $project->getEventManager();
        $list = $eventManager->getEvents();

        $result = array();

        foreach ($list as $o=>$e)
        {
            if(empty($e))
                continue;

            if($project->objectExists($o))
            {
                $object = $project->getObject($o);
                $eventObject = $object;

                while (method_exists($eventObject, 'getObject')){
                    $eventObject = $eventObject->getObject();
                }

                $events = $eventObject->getConfig()->getEvents()->__toArray();


                foreach ($e as $eName=>$eConfig)
                {
                    if(isset($events[$eName]))
                        $eConfig['params'] = $this->_convertParams($events[$eName]);
                    else
                        $eConfig['params'] = '';

                    unset($eConfig['code']);
                    $eConfig['is_local'] = true;
                    $result[] = $eConfig;

                }
            }
            else
            {
                /*
                 * Sub items with events
                 */
                foreach ($e as $eName=>$eConfig)
                {
                    if(isset($eConfig['params']) && is_array($eConfig['params']))
                        $eConfig['params'] = $this->_convertParams($eConfig['params']);
                    else
                        $eConfig['params'] = '';

                    unset($eConfig['code']);
                    $eConfig['is_local'] = true;
                    $result[] = $eConfig;
                }
            }
        }
        Response::jsonSuccess($result);
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
        $project = $this->_getProject();
        $object = $this->_getObject();

        $objectName = $object->getName();

        $objectEvents = $project->getEventManager()->getObjectEvents($objectName);

        if($object->isInstance()){
            $events = $object->getObject()->getConfig()->getEvents();
        }else{
            $events = $object->getConfig()->getEvents();
        }

        $result = array();
        $id =1;
        foreach ($events as $name=>$config)
        {
            if(isset($objectEvents[$name]) && !empty($objectEvents[$name]))
                $hasCode = true;
            else
                $hasCode = false;

            $result[$name] = array(
                'id'=>$id,
                'object'=>$objectName,
                'event'=>$name,
                'params'=>$this->_convertParams($config),
                'has_code'=>$hasCode,
                'is_local'=>false
            );
            $id++;
        }

        $localEvents = $project->getEventManager()->getLocalEvents($objectName);
        foreach ($localEvents as $name => $description)
        {
            if(isset($result[$name]))
                continue;

            $result[$name] = array(
                'id'=>$name,
                'object'=>$objectName,
                'event'=>$name,
                'params'=>$this->_convertParams($description['params']),
                'has_code'=>!empty($description['code']),
                'is_local'=>true
            );

        }
        Response::jsonSuccess(array_values($result));
    }

    protected function _getEvent()
    {
        $event = Request::post('event' , 'string' , false);
        if(!strlen($event) || $event === false)
            Response::jsonError($this->_lang->WRONG_REQUEST);
        return $event;
    }

    /**
     * Load event description
     */
    public function eventcodeAction()
    {
        $objectName = Request::post('object' , 'string' , '');
        $project = $this->_getProject();

        if(!strlen($objectName))
            Response::jsonError($this->_lang->WRONG_REQUEST);

        $event = $this->_getEvent();

        $eventManager = $project->getEventManager();

        if($eventManager->eventExists($objectName , $event))
            $info = $eventManager->getEventInfo($objectName , $event);
        else
            $info = array('code'=>'');

        if(isset($info['params']))
            $info['params'] = strip_tags($this->_convertParams($info['params']));
        else
            $info['params'] = '';


        Response::jsonSuccess($info);
    }

    /**
     * Update event description
     */
    public function saveeventAction()
    {
        $project = $this->_getProject();


        $name = Request::post('object', 'string', '');

        if(!strlen($name))
            Response::jsonError($this->_lang->WRONG_REQUEST);

        if($project->objectExists($name))
            $object = $this->_getObject();
        else
            $object = false;

        $event = $this->_getEvent();

        $eventManager = $project->getEventManager();

        if($object && $eventManager->isLocalEvent($object->getName() , $event))
        {
            $newName = Request::post('new_name' , Filter::FILTER_ALPHANUM , '');

            if(!strlen($newName))
                Response::jsonError($this->_lang->get('FILL_FORM') , array('new_name'=>$this->_lang->get('CANT_BE_EMPTY')));

            $params = Request::post('params', Filter::FILTER_STRING, '');
            $code = Request::post('code', Filter::FILTER_RAW, '');
            $buffer = Request::post('buffer', Filter::FILTER_INTEGER, false);

            if(empty($buffer)){
                $buffer = false;
            }

            if(!$eventManager->eventExists($object->getName(), $event))
                Response::jsonError($this->_lang->WRONG_REQUEST);

            if(!empty($params))
            {
                $params = explode(',' , trim($params));
                $paramsArray = array();
                foreach ($params as $k=>$v)
                {
                    $param = explode(' ', trim($v));
                    if(count($param) == 1)
                    {
                        $paramsArray[trim($v)] = '';
                    }else{
                        $pName = array_pop($param);
                        $ptype = trim(implode(' ', str_replace('  ', ' ',$param)));
                        $paramsArray[$pName] = $ptype;
                    }
                }
                $params = $paramsArray;
            }
            $eventManager->setEvent($object->getName(), $event, $code , $params , true, $buffer);

            if($newName!==$event)
            {
                if($eventManager->eventExists($object, $newName) || $object->getConfig()->getEvents()->isValid($newName))
                    Response::jsonError($this->_lang->get('FILL_FORM') , array('new_name'=>$this->_lang->get('SB_UNIQUE')));

                $eventManager->renameLocalEvent($object->getName() , $event , $newName);
            }
            $this->_storeProject();
            Response::jsonSuccess();
        }
        else
        {
            // update event action for std event
            $this->setcodeAction();
        }
    }

    public function setcodeAction()
    {
        $objectName = Request::post('object' , 'string' , '');
        $buffer = Request::post('buffer', Filter::FILTER_INTEGER, false);

        if(empty($buffer)){
            $buffer = false;
        }
        $project = $this->_getProject();

        if(!strlen($objectName))
            Response::jsonError($this->_lang->WRONG_REQUEST);

        $event = $this->_getEvent();
        $code = Request::post('code' , 'raw' , '');

        $project->getEventManager()->setEvent($objectName , $event , $code, false, false, $buffer);
        $this->_storeProject();
        Response::jsonSuccess();
    }

    public function removeeventAction()
    {
        $project = $this->_getProject();
        $event = $this->_getEvent();
        $object = Request::post('object' , 'string' , '');

        if(!strlen($object))
            Response::jsonError($this->_lang->WRONG_REQUEST);

        $eventManager = $project->getEventManager();

        if($eventManager->isLocalEvent($object, $event)){
            $eventManager->updateEvent($object, $event, '');
        }else{
            $eventManager->removeObjectEvent($object , $event);
        }
        $this->_storeProject();
        Response::jsonSuccess();
    }

    /**
     * Remove local event
     */
    public function removeeventdescriptionAction()
    {
        $project = $this->_getProject();
        $event = $this->_getEvent();

        $object = Request::post('object' , 'string' , '');

        if(!strlen($object))
            Response::jsonError($this->_lang->WRONG_REQUEST);

        $eventManager = $project->getEventManager();
        $eventManager->removeObjectEvent($object , $event);
        $this->_storeProject();
        Response::jsonSuccess();
    }

    /**
     * Create local event fore extended object
     */
    public function addlocaleventAction()
    {
        $project = $this->_getProject();
        $event = Filter::filterValue(Filter::FILTER_ALPHANUM, $this->_getEvent());
        $object = $this->_getObject();

        $eventManager = $project->getEventManager();

        if($eventManager->eventExists($object->getName(), $event) || $object->getConfig()->getEvents()->isValid($event)){
            Response::jsonError($this->_lang->get('SB_UNIQUE'));
        }

        $eventManager->setEvent($object->getName(), $event, '' , '' , true);
        $this->_storeProject();
        Response::jsonSuccess();
    }
}
