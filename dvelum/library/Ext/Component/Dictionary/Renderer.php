<?php
class Ext_Component_Dictionary_Renderer extends Ext_Component_Abstract_Renderer_Dictionary
{
    public function __construct($name)
    {
        $this->_name =  $name;
        $this->_dictionaryData = Dictionary::factory($this->_name)->getData();
    }
}