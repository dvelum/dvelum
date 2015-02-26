<?php
abstract class Ext_Property_Form_Field_Base extends Ext_Property_Component
{
	public $fieldSubTpl = self::Object;
	public $name = self::String;
	public $inputType = self::String;
	public $tabIndex = self::Numeric;
	public $invalidText = self::String;
	public $fieldCls = self::String;
	public $fieldStyle = self::String;
	public $fieldLabel = self::String;
	public $focusCls = self::String;
	public $dirtyCls = self::String;
	public $checkChangeEvents = self::Object;
	public $checkChangeBuffer = self::Numeric;
	public $labelAlign = self::String;
	public $labelPad  = self::Numeric;
	public $labelSeparator  = self::String;
	public $labelStyle  = self::String;
	public $labelWidth = self::Numeric;
	public $readOnly = self::Boolean;
	public $readOnlyCls = self::String;
	public $inputId = self::String;
	public $validateOnBlur = self::Boolean;
}