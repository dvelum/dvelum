<?php
class Ext_Property_Grid_Column_Number extends Ext_Property_Grid_Column
{
	public $format = self::String;

	static public $extend = 'Ext.grid.column.Number';
	static public $xtype = 'numbercolumn';
}