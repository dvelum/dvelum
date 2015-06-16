<?php
class Ext_Property_Data_Store extends Ext_Property_Data_Proxystore
{
	public $associatedEntity = self::Object;
	public $clearOnPageLoad = self::Boolean;
	public $clearRemovedOnLoad = self::Boolean;
	public $data = self::Object;
	public $role = self::Object;
	public $session = self::Object;

	static public $extend = 'Ext.data.Store';
}