<?php
class Ext_Property_Grid_Filter_Boolean extends Ext_Property_Grid_Filter
{
	public $defaultValue = self::Boolean;
	public $noText  = self::String;
	public $yesText = self::String;

	static public $extend = 'Ext.grid.filters.filter.Boolean';
}