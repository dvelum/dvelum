<?php
class Ext_Property_Data_Writer_Xml extends Ext_Property_Data_Writer
{
	public $defaultDocumentRoot  = self::String;
	public $documentRoot  = self::String;
	public $header  = self::String;
	public $record  = self::String;

	static public $extend = 'Ext.data.writer.Xml';
}
