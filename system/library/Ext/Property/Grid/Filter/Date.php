<?php
class Ext_Property_Grid_Filter_Date extends Ext_Property_Grid_Filter
{
    public $dateFormat  = self::String;
	public $fields = self::Object;
	public $maxDate = self::Object;
    public $minDate = self::Object;
    public $pickerDefaults = self::Object;

	static public $extend = 'Ext.grid.filters.filter.Date';
}