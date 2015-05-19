<?php
class Ext_Property_Form_Field_Spinner extends Ext_Property_Form_Field_Text
{
	public $keyNavEnabled = self::Boolean;
	public $mouseWheelEnabled = self::Boolean;
	public $spinDownEnabled = self::Boolean;
	public $spinUpEnabled = self::Boolean;

	static public $extend = 'Ext.form.field.Spinner';
	static public $xtype = 'spinnerfield';
}