<?php
abstract class Ext_Property_Form_Field_Picker extends Ext_Property_Form_Field_Text
{
	public $matchFieldWidth = self::Boolean;
	public $openCls = self::String;
	public $pickerAlign = self::String;
	public $pickerOffset = self::Object;

	static public $extend = 'Ext.form.field.Picker';
	static public $xtype = 'pickerfield';
}
