<?php
class Ext_Property_Grid_Column_Check extends Ext_Property_Grid_Column
{
	public $stopSelection = self::Boolean;

	static public $extend = 'Ext.grid.column.Check';
	static public $xtype = 'checkcolumn';
}
