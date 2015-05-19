<?php
class Ext_Property_Data_Proxy_Ajax extends Ext_Property_Data_Proxy_Server
{
	public $actionMethods = self::Object;
	public $binary = self::Boolean;
	public $headers = self::Object;
	public $paramsAsJson = self::Boolean;
	public $password = self::String;
	public $useDefaultXhrHeader = self::Boolean;
	public $username = self::String;
	public $withCredentials = self::Boolean;

	static public $extend = 'Ext.data.proxy.Ajax';
}
