<?php
class Ext_Property_Form_Field_File extends Ext_Property_Form_Field_Text
{
	public $buttonText = self::String;
	public $buttonOnly = self::Boolean;
	public $buttonMargin = self::Number;
	public $buttonConfig = self::Object;
	public $fileInputEl = self::Object;
	public $button = self::Object;
	public $fieldBodyCls = self::String;
	public $readOnly = self::Boolean;
	
	static public $extend = 'Ext.form.field.File';
	static public $xtype = 'fileuploadfield';
}