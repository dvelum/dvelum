<?php
class Ext_Property_Button extends Ext_Property_Component
{
	public $hidden = self::Boolean;
	public $disabled = self::Boolean;
	public $pressed = self::Boolean;
	public $text = self::String;
	public $icon = self::String;
	public $handler = self::Object;
	public $minWidth = self::Numeric;
	/*
     * {String/Object}
     */
	public $tooltip = self::String;
	public $toggleGroup = self::String;
	/*
     * {Boolean/Object}
     */
	public $repeat = self::Boolean;
	public $tabIndex = self::Numeric;
	public $allowDepress = self::Boolean;
	public $enableToggle = self::Boolean;
	public $toggleHandler = self::Object;
	public $menu = self::Object;
	public $menuAlign = self::String;
	public $textAlign = self::String;
	public $overflowText = self::String;
	public $iconCls = self::String;
	public $type = self::String;
	public $clickEvent = self::String;
	public $preventDefault = self::Boolean;
	public $handleMouseEvents = self::Boolean;
	public $tooltipType = self::String;
	public $baseCls = self::String;
	public $pressedCls = self::String;
	public $overCls = self::String;
	public $focusCls = self::String;
	public $menuActiveCls = self::String;
	public $href = self::String;
	public $hrefTarget = self::String;
	public $baseParams = self::Object;
	public $ariaRole = self::Object;
	public $scope = self::Object;
	public $scale = self::String;

	/**
	 * Whether or not to destroy any associated menu when this button is destroyed. The menu will be destroyed unless this is explicitly set to false.
	 */
	public $destroyMenu = self::Boolean;

	static public $extend = 'Ext.button.Button';
	static public $xtype = 'button';
}