<?php
class Ext_Property_Grid_Column extends Ext_Property_Container
{
	public $defaultWidth = self::Numeric;
	public $sortable = self::Boolean;
	public $weight = self::Numeric;
	public $align  = self::String;
	public $columns = self::Object;
	public $dataIndex  = self::String;
	public $draggable  = self::Boolean;
	public $editor  = self::Object;
	public $editable = self::Boolean;
	public $groupable = self::Boolean;
	public $hideable = self::Boolean;
	public $menuDisabled = self::Boolean;
	public $menuText = self::String;
	public $renderer = self::Object;
	public $resizable = self::Boolean;
	public $tdCls  = self::String;
	public $text  = self::String;
	public $summaryRenderer = self::Object;
	public $summaryType = self::String;
	public $flex = self::Numeric;
	public $locked = self::Boolean;
	
	static public $xtype = 'gridcolumn';
}