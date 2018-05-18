<?php
class Ext_Data_Store_Buffered extends Ext_Store{
    protected function _initDefaultProperties(){
        parent::_initDefaultProperties();
        $this->buffered = true;
    }
}