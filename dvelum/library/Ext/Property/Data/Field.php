<?php
class Ext_Property_Data_Field extends Ext_Property
{
    public $allowBlank = self::Boolean;
    public $allowNull = self::Boolean;
    public $calculate = self::Object;
    public $convert = self::Object;
    public $critical = self::Boolean;
    public $defaultValue = self::Object;
    public $depends = self::String;
    public $mapping = self::Object;
    public $name = self::String;
    public $persist = self::Boolean;
    public $reference = self::String;
    public $serialize = self::Object;
    public $sortType = self::String;
    public $unique = self::Boolean;
    public $validators = self::Object;

    // Dvelum designer property
    public $type = self::String;
    public $dateFormat = self::String;

    static public $extend = 'Ext.data.field.Field';
}