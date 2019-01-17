<?php

/**
 * Operations with Colum editor
 */
class Backend_Designer_Sub_Editor extends Backend_Designer_Sub_Properties
{

    protected function _getColumn()
    {
        /*
         * Grid
         */
        $o = parent::_getObject();
        $col = Request::post('column', 'string', false);

        if ($col === false || !$o->columnExists($col)) {
            Response::jsonError($this->_lang->WRONG_REQUEST);
        }
        return $o->getColumn($col);
    }

    protected function _getObject()
    {
        $column = $this->_getColumn();
        $object = $column->editor;

        if (empty($object)) {
            $object = Ext_Factory::object('Form_Field_Text');
            $object->setName(parent::_getObject()->getName() . '_' . $column->getName() . '_editor');
            $column->editor = $object;
            $this->_storeProject();
        }
        return $object;
    }

    protected function _setEditor(Ext_Object $editor)
    {
        $this->_getColumn()->editor = $editor;
    }

    /**
     * Remove column editor
     */
    public function removeAction()
    {
        $this->_getProject()->getEventManager()->removeObjectEvents($this->_getObject()->getName());
        $this->_getColumn()->editor = '';
        $this->_storeProject();
        Response::jsonSuccess();
    }

    /**
     * Change field type
     */
    public function changetypeAction()
    {
        $this->_checkLoaded();
        $object = $this->_getObject();
        $column = $this->_getColumn();
        $type = Request::post('type', 'string', false);
        $adapter = Request::post('adapter', 'string', false);
        $dictionary = Request::post('dictionary', 'string', false);

        if ($type === 'Form_Field_Adapter') {
            $newObject = Ext_Factory::object($adapter);
            /*
             * Invalid adapter
             */
            if (!$adapter || !strlen($adapter) || !class_exists($adapter))
                Response::jsonError($this->_lang->INVALID_VALUE, array('adapter' => $this->_lang->INVALID_VALUE));

            if ($adapter === 'Ext_Component_Field_System_Dictionary') {
                /*
                 * Inavalid dictionary
                 */
                if (!$dictionary || !strlen($dictionary))
                    Response::jsonError($this->_lang->INVALID_VALUE, array('dictionary' => $this->_lang->INVALID_VALUE));

                $newObject->dictionary = $dictionary;

            }
        } else {
            $newObject = Ext_Factory::object($type);
            /*
             * No changes
             */
            if ($type === $object->getClass())
                Response::jsonSuccess();
        }

        Ext_Factory::copyProperties($object, $newObject);
        $newObject->setName($object->getName());

        $this->_getProject()->getEventManager()->removeObjectEvents($newObject->getName());

        $this->_setEditor($newObject);
        $this->_storeProject();
        Response::jsonSuccess();
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

        $events = $object->getConfig()->getEvents();

        $result = array();
        $id = 1;
        foreach ($events as $name => $config) {
            if (isset($objectEvents[$name]) && !empty($objectEvents[$name]))
                $hasCode = true;
            else
                $hasCode = false;

            $result[] = array(
                'id' => $id,
                'object' => $objectName,
                'event' => $name,
                'params' => $this->_convertParams($config),
                'has_code' => $hasCode
            );
            $id++;
        }
        Response::jsonSuccess($result);
    }

    protected function _convertParams($config)
    {
        if (empty($config))
            return '';

        $paramsArray = [];

        foreach ($config as $pName => $pType)
            $paramsArray[] = '<span style="color:green;">' . $pType . '</span> ' . $pName;

        return implode(' , ', $paramsArray);
    }

    protected function _getEvent()
    {
        $event = Request::post('event', 'string', false);

        if (!strlen($event) || $event === false)
            Response::jsonError($this->_lang->WRONG_REQUEST);

        return $event;
    }

    public function eventcodeAction()
    {
        $objectName = $this->_getObject()->getName();
        $project = $this->_getProject();

        $event = $this->_getEvent();

        $eventManager = $project->getEventManager();

        if ($eventManager->eventExists($objectName, $event))
            $code = $eventManager->getEventCode($objectName, $event);
        else
            $code = '';

        Response::jsonSuccess(array('code' => $code));
    }


    public function saveeventAction()
    {
        $objectName = $this->_getObject()->getName();
        $project = $this->_getProject();

        $buffer = Request::post('buffer', Filter::FILTER_INTEGER, false);
        if (empty($buffer)) {
            $buffer = false;
        }

        $event = $this->_getEvent();
        $code = Request::post('code', 'raw', '');
        $events = $this->_getObject()->getConfig()->getEvents();
        $project->getEventManager()->setEvent($objectName, $event, $code, $events->$event, false, $buffer);
        $this->_storeProject();
        Response::jsonSuccess();
    }

    public function removeeventAction()
    {
        $event = $this->_getEvent();
        $name = $this->_getObject()->getName();
        $project = $this->_getProject();

        $project->getEventManager()->removeObjectEvent($name, $event);
        $this->_storeProject();
        Response::jsonSuccess();
    }
}