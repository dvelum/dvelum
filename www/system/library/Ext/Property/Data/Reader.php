<?php
class Ext_Property_Data_Reader extends Ext_Property{
	public $idProperty = self::String;
	public $implicitIncludes = self::Boolean;
	public $messageProperty = self::String;
	public $root = self::String;
	public $successProperty = self::String;
	public $totalProperty = self::String;
	public $type = self::String;
	
	static public $xtype = '';
}