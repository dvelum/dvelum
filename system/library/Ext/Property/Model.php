<?php
class Ext_Property_Model extends Ext_Property
{
	public $associations = self :: Object;
	public $belongsTo = self :: Object;	
	public $defaultProxyType = self :: String;
	public $fields = self::Object;
	public $idProperty = self::String;	
	public $idgen = self::String;
	public $listeners = self::Object;
	public $persistenceProperty = self::String;
	public $proxy = self::Object;
	public $validations = self::Object;
	
	static public $extend = 'Ext.data.Model';
	static public $xtype = 'model';
}