<?php
class Ext_Property_Container extends Ext_Property_Component
{
	public $activeItem = self::String;
	public $anchorSize = self::Object;
	public $autoDestroy = self::Boolean;
	public $bubbleEvents = self::String;
	public $defaultFocus = self::String;
	public $defaultType = self::String;
	public $defaults = self::Object;
	public $detachOnRemove = self::Boolean;
	public $items = self::Object;
	public $layout = self::Object;
	public $referenceHolder = self::Boolean;
	public $suspendLayout = self::Boolean;
    
    static public $extend = 'Ext.container.Container';
    static public $xtype = 'container';
}