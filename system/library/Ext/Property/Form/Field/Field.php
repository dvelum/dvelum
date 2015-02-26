<?php
abstract class Ext_Property_Form_Field_Field extends Ext_Property_Form_Field_Base
{
	public $isFormField = self::Boolean;
	public $value = self::Object;
	public $name = self::String;
	public $disabled = self::Boolean;
	public $submitValue = self::Boolean;
	public $validateOnChange = self::Boolean;
	public $fieldLabel = self::String;
}