<?php
abstract class Ext_Property_Form_Field_Picker extends Ext_Property_Form_Field_Trigger
{	
	public $matchFieldWidth = self::Boolean;
	public $pickerAlign = self::String;
	public $pickerOffset = self::Object;	
	public $openCls = self::String;	
	public $isExpanded = self::Boolean;
	public $editable = self::Boolean;
}
