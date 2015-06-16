<?php
class Ext_Property_Data_Reader_Json extends Ext_Property_Data_Reader
{
	public $metaProperty = self::String;
	public $preserveRawData = self::Boolean;
	public $record = self::String;
	public $useSimpleAccessors = self::Boolean;

	static public $extend = 'Ext.data.reader.Json';
}