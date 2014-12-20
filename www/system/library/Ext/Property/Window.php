<?php
class Ext_Property_Window extends Ext_Property_Panel
{
	public $x = self::Numeric;
	public $y = self::Numeric;
	/*
     * {String/Ext.Element}
     */
	public $animateTarget = self::String;
	/*
     * {String/Number/Ext.Component}
     */
	public $defaultFocus = self::Object;
	public $onEsc = self::Object;
	public $collapsed = self::Boolean;
	public $maximized = self::Boolean;
	public $baseCls = self::String;
	/*
     *{Boolean/Object}
     */
	public $resizable = self::Boolean;
	public $draggable = self::Boolean;
	public $constrain = self::Boolean;
	public $constrainHeader = self::Boolean;
	public $plain = self::Boolean;
	public $minimizable = self::Boolean;
	public $maximizable = self::Boolean;
	public $minHeight = self::Numeric;
	public $minWidth = self::Numeric;
	public $modal = self::Boolean;
	public $expandOnShow = self::Boolean;
	public $collapsible = self::Boolean;
	public $closable = self::Boolean;
	public $hidden = self::Boolean;
	public $autoRender = self::Boolean;
	public $hideMode = self::String;
	public $floating = self::Boolean;
	public $ariaRole = self::String;
	public $itemCls = self::String;
	public $overlapHeader = self::Boolean;
	public $ignoreHeaderBorderManagement = self::Boolean;
	
	static public $extend = 'Ext.Window';
	static public $xtype = 'window';
}