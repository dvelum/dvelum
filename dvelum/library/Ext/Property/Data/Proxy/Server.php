<?php
abstract class Ext_Property_Data_Proxy_Server extends Ext_Property_Data_Proxy
{
	public $api = self::Object;
	public $cacheString = self::String;
	public $directionParam = self::String;
	public $extraParams = self::Object;
	public $filterParam = self::String;
	public $groupDirectionParam = self::String;
	public $groupParam = self::String;
	public $idParam = self::String;
	public $limitParam = self::String;
	public $noCache = self::Boolean;
	public $pageParam = self::String;
	public $simpleGroupMode = self::Boolean;
	public $simpleSortMode = self::Boolean;
	public $sortParam = self::String;
	public $startParam = self::String;
	public $timeout = self::Number;
	public $url = self::String;

	static public $extend = 'Ext.data.proxy.Server';
}