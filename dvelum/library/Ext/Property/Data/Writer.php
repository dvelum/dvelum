<?php
class Ext_Property_Data_Writer extends Ext_Property
{
	public $nameProperty = self::String;
	public $writeAllFields = self::Boolean;
	public $type = self::String;

	static public $extend = 'Ext.data.writer.Writer';
}
