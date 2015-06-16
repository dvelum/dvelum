<?php
class Ext_Property_Button extends Ext_Property_Component
{
	public $allowDepress = self::Boolean;
	public $arrowAlign = self::String;
	public $arrowCls = self::String;
	public $arrowVisible = self::Boolean;
	public $baseParams = self::Object;
	public $clickEvent = self::String;
	public $destroyMenu = self::Boolean;
	public $enableToggle = self::Boolean;
	public $glyph = self::String;
	public $handleMouseEvents = self::Boolean;
	public $handler = self::Object;
	public $href = self::String;
	public $hrefTarget = self::String;
	public $icon = self::String;
	public $iconAlign = self::String;
	public $iconCls = self::String;
	public $menu = self::Object;
	public $menuAlign = self::String;
	public $overflowText = self::String;
	public $params = self::Object;
	public $pressed = self::Boolean;
	public $preventDefault = self::Boolean;
	public $repeat = self::Boolean;
	public $scale = self::String;
	public $scope = self::Object;
	public $showEmptyMenu = self::Boolean;
	public $text = self::String;
	public $textAlign = self::String;
	public $toggleGroup = self::String;
	public $toggleHandler = self::Object;
	public $tooltip = self::String;
	public $tooltipType = self::String;
	public $value = self::String;

	static public $extend = 'Ext.button.Button';
	static public $xtype = 'button';
}