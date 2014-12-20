<?php
class Ext_Property_Toolbar extends Ext_Property_Container
{
	public $defaultType = self::String;
	public $vertical = self::Boolean;
	public $layout = self::String;
	public $enableOverflow = self::Boolean;
	public $menuTriggerCls = self::String;
	
	public $dock = self::String;
		
	static public $extend = 'Ext.toolbar.Toolbar';
	static public $xtype = 'toolbar';
}