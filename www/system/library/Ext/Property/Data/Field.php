<?php
class Ext_Property_Data_Field extends Ext_Property{
    
    public $convert = self::Object;
    public $dateFormat = self::String;
    public $defaultValue = self::Object;
    public $mapping = self::Object;
    public $persist = self::Boolean;
    public $name = self::String;
    public $sortDir = self::String;
    public $sortType = self::Object;
    public $type = self::String;
    public $useNull = self::Boolean;

    static public $xtype = '';
}