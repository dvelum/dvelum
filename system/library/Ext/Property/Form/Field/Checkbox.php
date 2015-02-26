<?php
class Ext_Property_Form_Field_Checkbox extends Ext_Property_Form_Field_Picker
{	
	public $focusCls = self::String;
	public $fieldCls = self::String;
	public $fieldBodyCls = self::String;
	public $checked = self::Boolean;
	public $checkedCls = self::String;
	public $boxLabel = self::String;
	public $boxLabelCls = self::String;
	public $boxLabelAlign = self::String;
	public $inputValue = self::String;
	public $uncheckedValue = self::String;
	public $handler = self::Object;
	public $scope = self::Object;
	
	static public $extend = 'Ext.form.field.Checkbox';
	static public $xtype = 'checkbox';
}