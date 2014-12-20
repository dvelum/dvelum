<?php
class Ext_Property_Form_Checkboxgroup extends Ext_Property_Form_Fieldcontainer
{
	public $name = self::String;
	public $items = self::Object;
	public $columns = self::Object;
	public $vertical = self::Boolean;
	public $allowBlank = self::Boolean;
	public $blankText = self::String;
	public $fieldBodyCls = self::String;
	
	
	static public $extend = 'Ext.form.CheckboxGroup';
	static public $xtype = 'checkboxgroup';
}