<?php
abstract class Ext_Property_Form_Field_Field extends Ext_Property_Form_Field_Base
{
	public $submitValue = self::Boolean;
	public $validateOnChange = self::Boolean;
	public $validation = self::Boolean;
	public $validationField = self::Object;
	public $value = self::Object;
	public $valuePublishEvent = self::String;

	static public $extend = 'Ext.form.field.Field';
}