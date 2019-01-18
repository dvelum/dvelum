<?php

/**
 * Operations with filters
 */
class Backend_Designer_Sub_Filter extends Backend_Designer_Sub_Properties
{
    protected function _getObject()
    {
        $o = parent::_getObject();
        return $o->getViewObject();
    }

    /**
     * Change field type
     */
    public function changetypeAction()
    {
        $this->_checkLoaded();
        $object = $this->_getObject();
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
        parent::_getObject()->setViewObject($newObject);
        $this->_storeProject();
        Response::jsonSuccess();
    }
}