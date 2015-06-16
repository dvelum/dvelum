<?php
class Ext_Property_Form_Field_Time extends Ext_Property_Form_Field_Picker
{
	public $valueField = self::String;
	public $altFormats = self::String;
	public $displayField = self::String;
	public $format = self::String;
	public $increment = self::Number;
	public $maxText = self::String;
	public $maxValue = self::String;
	public $minText = self::String;
	public $minValue = self::String;
	public $pickerMaxHeight = self::Number;
	public $queryMode = self::String;
	public $selectOnTab = self::Boolean;
	public $snapToIncrement = self::Boolean;
	public $submitFormat = self::String;
	public $triggerCls = self::String;

	static public $extend = 'Ext.form.field.Time';
	static public $xtype = 'timefield';
}