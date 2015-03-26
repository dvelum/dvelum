<?php
class Ext_Property_Buttongroup extends Ext_Property_Panel
{
    public $baseCls  = self::String;
    public $columns = self::Number;
    public $defaultButtonUI = self::String;
    public $defaultType = self::String;
    public $frame = self::Boolean;
    public $layout = self::Object;
    public $titleAlign  = self::String;

    static public $extend = 'Ext.container.ButtonGroup';
    static public $xtype = 'buttongroup';
}