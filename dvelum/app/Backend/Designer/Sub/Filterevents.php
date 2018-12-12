<?php
class Backend_Designer_Sub_Filterevents extends Backend_Designer_Sub_Events
{
    protected function _getObject()
    {
        $o = parent::_getObject();
        return $o->getViewObject();
    }
}