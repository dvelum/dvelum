<?php
class Ext_Property_Form_Field_Text extends Ext_Property_Form_Field_Field
{
	public $allowBlank = self::Boolean;
	public $allowOnlyWhitespace = self::Boolean;
	public $blankText = self::String;
	public $disableKeyFilter = self::Boolean;
	public $editable = self::Boolean;
	public $emptyCls = self::String;
	public $emptyText = self::String;
	public $enableKeyEvents = self::Boolean;
	public $enforceMaxLength = self::Boolean;
	public $grow = self::Boolean;
	public $growAppend = self::String;
	public $growMax = self::Number;
	public $growMin = self::Number;
	public $hideTrigger = self::Boolean;
	public $inputWrapCls = self::String;
	public $maskRe = self::Object;
	public $maxLength = self::Number;
	public $maxLengthText = self::String;
	public $minLength = self::Number;
	public $minLengthText = self::String;
	public $regex = self::Object;
	public $regexText = self::String;
	public $repeatTriggerClick = self::Boolean;
	public $requiredCls = self::String;
	public $selectOnFocus = self::Boolean;
	public $stripCharsRe = self::Object;
	public $triggerWrapCls = self::String;
	public $triggers = self::Object;
	public $validateBlank = self::Boolean;
	public $validator = self::Object;
	public $vtype = self::String;
	public $vtypeText = self::String;
	public $value = self::String;


	// dvelum validation field
	public $initialPassField = self::String;

	static public $extend = 'Ext.form.field.Text';
	static public $xtype = 'textfield';
}