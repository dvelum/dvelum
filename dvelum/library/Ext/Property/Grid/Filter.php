<?php
abstract class Ext_Property_Grid_Filter extends Ext_Property
{
	public $active  = self::Boolean;
	public $dataIndex = self::String;
	public $itemDefaults = self::Object;
	public $menuDefaults = self::Object;
	public $updateBuffer = self::Numeric;
	public $listeners = self::Object;

    //dvelum proprety
	public $type = self::String;

	static public $xtype = '';
	static public $extend = 'Ext.grid.filters.filter.Base';
}