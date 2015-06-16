<?php
class Ext_Property_Grid_Column_Boolean extends Ext_Property_Grid_Column
{
	public $falseText = self::String;
	public $trueText =  self::String;
	public $undefinedText = self::String;

	static public $extend = 'Ext.grid.column.Boolean';
	static public $xtype = 'booleancolumn';
}