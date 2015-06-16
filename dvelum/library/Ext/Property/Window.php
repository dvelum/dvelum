<?php
class Ext_Property_Window extends Ext_Property_Panel
{
	public $animateTarget = self::String;
	public $expandOnShow = self::Boolean;
	public $ghost = self::Boolean;
	public $hideShadowOnDeactivate = self::Boolean;
	public $maximizable = self::Boolean;
	public $maximized = self::Boolean;
	public $minimizable = self::Boolean;
	public $monitorResize = self::Boolean;
	public $onEsc = self::Object;
	public $plain = self::Boolean;
	public $x = self::Number;
	public $y = self::Number;
	
	static public $extend = 'Ext.window.Window';
	static public $xtype = 'window';
}