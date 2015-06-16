<?php
class Ext_Property_Form_Field_Checkbox extends Ext_Property_Form_Field_Picker
{
	public $afterBoxLabelTextTpl = self::Object;
	public $afterBoxLabelTpl = self::Object;
	public $beforeBoxLabelTextTpl = self::Object;
	public $beforeBoxLabelTpl = self::Object;
	public $boxLabel = self::String;
	public $boxLabelAlign = self::String;
	public $boxLabelAttrTpl = self::Object;
	public $boxLabelCls = self::String;
	public $checked = self::Boolean;
	public $checkedCls = self::String;
	public $handler = self::Object;
	public $inputValue = self::String;
	public $scope = self::Object;
	public $uncheckedValue = self::String;
	
	static public $extend = 'Ext.form.field.Checkbox';
	static public $xtype = 'checkbox';
}