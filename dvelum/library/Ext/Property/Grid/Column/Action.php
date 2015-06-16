<?php
class Ext_Property_Grid_Column_Action extends Ext_Property_Grid_Column
{
	public $altText = self::String;
	public $getClass = self::Object;
	public $getTip = self::Object;
	public $handler = self::Object;
	public $icon = self::String;
	public $iconCls = self::String;
	public $isDisabled = self::Object;
	public $stopSelection = self::Boolean;

	static public $extend = 'Ext.grid.column.Action';
	static public $xtype = 'actioncolumn';
}