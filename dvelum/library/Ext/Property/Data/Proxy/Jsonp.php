<?php
class Ext_Property_Data_Proxy_Jsonp extends Ext_Property_Data_Proxy_Server
{
	public $autoAppendParams = self::Boolean;
	public $callbackKey = self::String;
	public $recordParam = self::String;

	static public $extend = 'Ext.data.proxy.Jsonp';
}