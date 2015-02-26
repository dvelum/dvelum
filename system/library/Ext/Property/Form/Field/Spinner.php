<?php
class Ext_Property_Form_Field_Spinner extends Ext_Property_Form_Field_Trigger
{
	public $trigger1Cls = self::String;
	public $trigger2Cls = self::String;
	public $spinUpEnabled = self::Boolean;
	public $spinDownEnabled = self::Boolean;
	public $keyNavEnabled = self::Boolean;
	public $mouseWheelEnabled = self::Boolean;
	public $repeatTriggerClick = self::Boolean;
	
	static public $extend = 'Ext.form.field.Spinner';
	static public $xtype = 'spinnerfield';
}