<?php
class Ext_Property_Data_Store extends Ext_Property_Data_Abstractstore
{
	public $buffered = self::Boolean;
	public $clearOnPageLoad = self::Boolean;
	public $data = self::Object;	
	public $listeners = self::Object;
	public $pageSize = self::Numeric;	
	public $purgePageCount = self::Numeric;		
	public $remoteFilter = self::Boolean;
	public $remoteGroup = self::Boolean;
	public $remoteSort = self::Boolean;
	public $sortOnFilter = self::Boolean;
	public $sortOnLoad = self::Boolean;
	public $sortRoot = self::String;
	public $sorters = self ::Object;
	public $groupField = self::String;
	
	static public $extend = 'Ext.data.Store';
	static public $xtype = 'store';
}