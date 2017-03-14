<?php
class Backend_Designer_Sub_FilterEvents extends Backend_Designer_Sub_Events
{
    protected function _getObject()
    {
        $o = parent::_getObject();
        return $o->getViewObject();
    }
}