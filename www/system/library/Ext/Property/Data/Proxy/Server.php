<?php
abstract class Ext_Property_Data_Proxy_Server extends Ext_Property_Data_Proxy
{
	public $api = self::Object;
	public $cacheString = self::String;
	public $directionParam = self::String;
	public $extraParams = self::Object;
	public $filterParam  = self::String;
	public $groupParam  = self::String;
	public $limitParam  = self::String;
	public $noCache = self::Boolean;
	public $pageParam  = self::String;
	public $simpleSortMode =self::Boolean;
	public $sortParam  = self::String;
	public $startParam  = self::String;
	public $timeout = self::Numeric;
	public $url  = self::String;
} 