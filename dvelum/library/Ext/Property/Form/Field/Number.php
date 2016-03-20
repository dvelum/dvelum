<?php
class Ext_Property_Form_Field_Number extends Ext_Property_Form_Field_Spinner
{
	public $allowDecimals = self::Boolean;
	public $allowExponential = self::Boolean;
	public $autoStripChars = self::Boolean;
	public $baseChars = self::String;
	public $decimalPrecision = self::Number;
	public $decimalSeparator = self::String;
	public $maxText = self::String;
	public $maxValue = self::Number;
	public $minText = self::String;
	public $minValue = self::Number;
	public $nanText = self::String;
	public $negativeText = self::String;
	public $step = self::Number;
	public $submitLocaleSeparator = self::Boolean;
	public $value = self::Number;

	static public $extend = 'Ext.form.field.Number';
	static public $xtype = 'numberfield';
}