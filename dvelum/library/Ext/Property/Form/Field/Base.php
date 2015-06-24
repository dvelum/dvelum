<?php
abstract class Ext_Property_Form_Field_Base extends Ext_Property_Component
{
	use Ext_Property_Form_Labelable;

	public $checkChangeBuffer = self::Number;
	public $checkChangeEvents = self::Object;
	public $dirtyCls = self::String;
	//public $fieldBodyCls = self::String;
	public $fieldCls = self::String;
	public $fieldStyle = self::String;
	public $fieldSubTpl = self::Object;
	public $inputAttrTpl = self::Object;
	public $inputId = self::String;
	public $inputType = self::String;
	public $invalidText = self::String;
	public $isTextInput = self::Boolean;
	public $name = self::String;
	public $readOnly = self::Boolean;
	public $readOnlyCls = self::String;
	public $validateOnBlur = self::Boolean;

	static public $extend = 'Ext.form.field.Base';
	static public $xtype = 'field';
}