<?php
class Ext_Property_Data_Proxy_Direct extends Ext_Property_Data_Proxy_Server{
	public $api = self::Object;
	public $directFn = self::Object;
	public $extraParams = self::Object;
	public $paramOrder = self::String;
	public $paramsAsHash = self::Boolean;
}

