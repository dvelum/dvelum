<?php
class Ext_Property_Data_Reader extends Ext_Property{
	public $implicitIncludes = self::Boolean;
	public $keepRawData = self::Boolean;
	public $messageProperty = self::String;
	public $model = self::String;
	public $proxy = self::Object;
	public $readRecordsOnFailure = self::Boolean;
	public $rootProperty = self::String;
	public $successProperty = self::String;
	public $totalProperty = self::String;
	public $transform = self::Object;
	public $typeProperty = self::String;

	// dvelum designer Property
	public $type = self::String;

	static public $extend = 'Ext.data.reader.Reader';
}