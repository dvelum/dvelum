<?php

class Ext_Property_Menu_Item extends Ext_Property_Component
{
	public $activeCls = self::String;
	public $ariaRole = self::String;
	public $clickHideDelay = self::Number;
	public $destroyMenu = self::Boolean;
	public $glyph = self::String;
	public $handler = self::Object;
	public $hideOnClick = self::Boolean;
	public $href = self::String;
	public $hrefTarget = self::String;
	public $icon = self::String;
	public $iconCls = self::String;
	public $menu = self::Object;
	public $menuAlign = self::String;
	public $menuExpandDelay = self::Number;
	public $menuHideDelay = self::Number;
	public $plain = self::Boolean;
	public $text = self::String;
	public $tooltip = self::String;
	public $tooltipType = self::String;

    public static $extend = 'Ext.menu.Item';
    public static $xtype = 'menuitem';
}