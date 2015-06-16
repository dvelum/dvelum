<?php
class Ext_Property_Data_Proxy_Rest extends Ext_Property_Data_Proxy_Ajax
{
	public $appendId = self::Boolean;
	public $format = self::String;

	static public $extend = 'Ext.data.proxy.Rest';
}



