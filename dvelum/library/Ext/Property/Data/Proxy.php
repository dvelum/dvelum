<?php
class Ext_Property_Data_Proxy extends Ext_Property
{
	public $batchActions = self::Boolean;
	public $batchOrder = self::String;
	public $model = self::String;
	public $reader = self::Object;
	public $writer = self::Object;

	// Dvelum designer property
	public $type = self::String;

	static public $extend = 'Ext.data.proxy.Proxy';
}