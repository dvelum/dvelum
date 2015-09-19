<?php
abstract class Ext_Property_Data_Abstractstore extends Ext_Property
{
	public $autoDestroy = self::Boolean;
	public $filters = self::Object;
	public $groupDir = self::String;
	public $groupField = self::String;
	public $grouper = self::Object;
	public $listeners = self::Object;
	public $pageSize = self::Number;
	public $remoteFilter = self::Boolean;
	public $remoteSort = self::Boolean;
	public $sorters = self::Object;
	public $statefulFilters = self::Boolean;
	public $storeId = self::String;

	static public $extend = 'Ext.data.AbstractStore';
}