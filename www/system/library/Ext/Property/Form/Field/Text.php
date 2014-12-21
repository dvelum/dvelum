<?php
class Ext_Property_Form_Field_Text extends Ext_Property_Form_Field_Field
{
	public $enableKeyEvents = self::Boolean;
	public $vtypeText = self::String;
	public $stripCharsRe = self::Object;
	public $size = self::Number;
	public $grow = self::Boolean;
	public $growMin = self::Number;
	public $growMax = self::Number;
	public $growAppend = self::String;
	public $vtype = self::String;
	public $maskRe = self::Object;
	public $disableKeyFilter = self::Boolean;
	public $allowBlank = self::Boolean;
	public $minLength = self::Number;
	public $maxLength = self::Number;
	public $enforceMaxLength = self::Boolean;
	public $minLengthText = self::String;
	public $maxLengthText = self::String;
	public $selectOnFocus = self::Boolean;
	public $blankText = self::String;
	public $validator = self::Object;
	public $regex = self::Object;
	public $regexText = self::String;
	public $emptyText = self::String;
	public $emptyCls = self::String;
	public $componentLayout = self::Boolean;
	public $value = self::String;

	public $initialPassField = self::String;

	static public $extend = 'Ext.form.field.Text';
	static public $xtype = 'textfield';
}