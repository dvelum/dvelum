<?php
class Ext_Data_Store_Tree extends Ext_Store{
    protected function _initDefaultProperties(){
        parent::_initDefaultProperties();
        $this->root = '{"text":"","expanded":false,"id":""}';
    }
}