<?php
class Ext_Property_Grid_Column_Date extends Ext_Property_Grid_Column
{
	public $format = self::String;

	static public $extend = 'Ext.grid.column.Date';
	static public $xtype = 'datecolumn';
}