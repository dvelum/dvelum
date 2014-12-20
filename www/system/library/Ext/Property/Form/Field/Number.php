<?php
class Ext_Property_Form_Field_Number extends Ext_Property_Form_Field_Spinner
{
	public $stripCharsRe = self::Object;
	public $maskRe = self::Object;
	public $allowDecimals = self::Boolean;
	public $decimalSeparator = self::String;
	public $decimalPrecision = self::Number;
	public $minValue = self::Number;
	public $maxValue = self::Number;
	public $step = self::Number;
	public $minText = self::String;
	public $maxText = self::String;
	public $nanText = self::String;
	public $negativeText = self::String;
	public $baseChars = self::String;
	public $autoStripChars = self::Boolean;
	
	static public $extend = 'Ext.form.field.Number';
	static public $xtype = 'numberfield';
}