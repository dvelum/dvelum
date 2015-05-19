<?php
class Ext_Property_Data_Writer_Json extends Ext_Property_Data_Writer{
	public $allowSingle = self::Boolean;
	public $encode = self::Boolean;
	public $expandData = self::Boolean;
	public $rootProperty  = self::String;

	static public $extend = 'Ext.data.writer.Json';
}
