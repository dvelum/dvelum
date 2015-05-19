<?php
class Ext_Property_Toolbar extends Ext_Property_Container
{
	public $defaultButtonUI = self::String;
	public $defaultFieldUI = self::String;
	public $defaultFooterButtonUI = self::String;
	public $defaultFooterFieldUI = self::String;
	public $enableOverflow = self::Boolean;
	public $overflowHandler = self::String;
	public $vertical = self::Boolean;
		
	static public $extend = 'Ext.toolbar.Toolbar';
	static public $xtype = 'toolbar';
}