<?php
abstract class Ext_Grid_Filter extends Ext_Object
{
    public function getType()
    {
        return strtolower(str_replace('Ext_Grid_Filter_', '', get_called_class()));
    }

    public function __toString()
    {
        $this->_config->type = $this->getType();
        return parent::__toString();
    }
}