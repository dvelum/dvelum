<?php
class Ext_Property_Grid_Filter_List extends Ext_Property_Grid_Filter
{
	public $idField = self::String;
	public $labelField = self::String;
	public $labelIndex = self::String;
	public $loadOnShow = self::Boolean;
	public $loadingText = self::String;
	public $options = self::Object;
	public $single = self::Boolean;
	public $store = self::Object;

	static public $extend = 'Ext.grid.filters.filter.List';
}