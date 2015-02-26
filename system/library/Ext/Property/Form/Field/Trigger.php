<?php
abstract class Ext_Property_Form_Field_Trigger extends Ext_Property_Form_Field_Text
{
	public $triggerCls = self::String;
	public $triggerBaseCls = self::String;
	public $triggerWrapCls = self::String;
	public $hideTrigger = self::Boolean;
	public $editable = self::Boolean;
	public $readOnly = self::Boolean;
	public $selectOnFocus = self::Boolean;
	public $repeatTriggerClick = self::Boolean;
}