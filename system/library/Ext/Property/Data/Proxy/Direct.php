<?php
class Ext_Property_Data_Proxy_Direct extends Ext_Property_Data_Proxy_Server
{
	public $directFn = self::Object;
	public $metadata = self::Object;
	public $paramOrder = self::String;
	public $paramsAsHash = self::Boolean;

	static public $extend = 'Ext.data.proxy.Direct';
}

