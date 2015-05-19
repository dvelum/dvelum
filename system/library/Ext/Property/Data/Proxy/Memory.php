<?php
class Ext_Property_Data_Proxy_Memory extends Ext_Property_Data_Proxy_Client
{
	public $data = self::Object;
	public $enablePaging = self::Boolean;

	static public $extend = 'Ext.data.proxy.Memory';
}