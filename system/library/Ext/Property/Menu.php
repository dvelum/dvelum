<?php
class Ext_Property_Menu extends Ext_Property_Panel
{
	public $allowOtherMenus = self::Boolean;
	public $ariaRole = self::String;
	public $ignoreParentClicks = self::Boolean;
	public $plain = self::Boolean;
	public $showSeparator = self::Boolean;

    public static $extend = 'Ext.menu.Menu';
    public static $xtype = 'menu';
}