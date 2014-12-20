<?php
class Ext_Property_Form_Field_Time extends Ext_Property_Form_Field_Picker
{
	public $triggerCls = self::String;
	/*
     * {Date/String}
     */
	public $minValue = self::String;
	/*
     * {Date/String}
     */
	public $maxValue = self::String;
	public $minText = self::String;
	public $maxText = self::String;
	public $invalidText = self::String;
	public $format = self::String;
	public $submitFormat = self::String;
	public $altFormats = self::String;
	public $increment = self::Number;
	public $pickerMaxHeight = self::Number;
	public $selectOnTab = self::Boolean;
	
	static public $extend = 'Ext.form.field.Time';
	static public $xtype = 'timefield';
}