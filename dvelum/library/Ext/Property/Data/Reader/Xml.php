<?php
class Ext_Property_Data_Reader_Xml extends Ext_Property_Data_Reader
{
	public $record = self::String;
	public $namespace = self::String;

	static public $extend = 'Ext.data.reader.Xml';
}