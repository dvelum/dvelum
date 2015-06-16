<?php
class Ext_Property_Toolbar_Paging extends Ext_Property_Toolbar
{
	public $store = self::Object;
	public $afterPageText = self::String;
	public $beforePageText = self::String;
	public $displayInfo = self::Boolean;
	public $displayMsg = self::String;
	public $emptyMsg = self::String;
	public $firstText = self::String;
	public $inputItemWidth = self::Number;
	public $lastText = self::String;
	public $nextText = self::String;
	public $prependButtons = self::Boolean;
	public $prevText = self::String;
	public $refreshText = self::String;

	static public $extend = 'Ext.toolbar.Paging';
	static public $xtype = 'pagingtoolbar';
}