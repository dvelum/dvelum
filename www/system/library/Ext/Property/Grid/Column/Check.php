<?php
class Ext_Property_Grid_Column_Check extends Ext_Property_Grid_Column{
	public $stopSelection = self::Boolean;
	public $tdCls =  self::String;

	static public $xtype = 'checkcolumn';
}
