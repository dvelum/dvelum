<?php
class Ext_Property_Data_Proxystore extends Ext_Property_Data_Abstractstore
{
	public $autoLoad = self::Boolean;
	public $autoLoadDelay = self::Number;
	public $autoSync = self::Boolean;
	public $batchUpdateMode = self::String;
	public $fields = self::Object;
	public $model = self::String;
	public $proxy = self::Object;
	public $sortOnLoad = self::Boolean;
	public $trackRemoved = self::Boolean;

	static public $extend = 'Ext.data.ProxyStore';
}