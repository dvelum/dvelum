<?php
class Ext_Property_Form_Field_Date extends Ext_Property_Form_Field_Picker
{
	public $altFormats = self::String;
	public $disabledDatesself = self::Object;
	public $disabledDatesText = self::String;
	public $disabledDays = self::Object;
	public $disabledDaysText = self::String;
	public $format = self::String;
	public $invalidText  = self::String;	
	public $maxText = self::String;
	public $maxValue = self::String;
	public $minText = self::String;
	public $minValue = self::String;
	public $showToday = self::Boolean;
	public $startDay = self::Numeric;
	public $submitFormat = self::String;
	public $triggerCls = self::String;

	
	static public $extend = 'Ext.form.field.Date';
	static public $xtype = 'datefield';
}