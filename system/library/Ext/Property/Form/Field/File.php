<?php
class Ext_Property_Form_Field_File extends Ext_Property_Form_Field_Text
{
	public $buttonConfig = self::Object;
	public $buttonMargin = self::Number;
	public $buttonOnly = self::Boolean;
	public $buttonText = self::String;
	public $clearOnSubmit = self::Boolean;

	static public $extend = 'Ext.form.field.File';
	static public $xtype = 'fileuploadfield';
}