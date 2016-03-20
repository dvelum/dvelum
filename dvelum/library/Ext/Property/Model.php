<?php
class Ext_Property_Model extends Ext_Property
{
	public $associations = self::Object;
	public $belongsTo = self::Object;
	public $clientIdProperty = self::String;
	public $convertOnSet = self::Boolean;
	public $fields = self::Object;
	public $hasMany = self::String;
	public $idProperty = self::String;
	public $identifier = self::String;
	public $manyToMany = self::Object;
	public $proxy = self::Object;
	public $schema = self::String;
	public $validationSeparator = self::String;
	public $validators = self::Object;
	public $versionProperty = self::String;
	
	static public $extend = 'Ext.data.Model';
	static public $xtype = '';
}