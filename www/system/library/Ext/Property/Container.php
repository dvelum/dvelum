<?php
class Ext_Property_Container extends Ext_Property_Component
{
	public $suspendLayout = self::Boolean;
    public $autoDestroy = self::Boolean;
    public $defaultType = self::String;
    public $isContainer = self::Boolean;
    public $layoutCounter = self::Numeric;
    public $baseCls = self::String;
    public $bubbleEvents = self::Object;
    public $region = self::String;
    public $flex = self::Number;
    
    static public $extend = 'Ext.container.Container';
    static public $xtype = 'container';
}