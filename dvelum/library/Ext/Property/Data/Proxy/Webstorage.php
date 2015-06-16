<?php
abstract class Ext_Property_Data_Proxy_Webstorage extends Ext_Property_Data_Proxy_Client
{
	public $id = self::String;

	static public $extend = 'Ext.data.proxy.Webstorage';
}