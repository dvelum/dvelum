<?php
class Ext_Property_Form_Checkboxgroup extends Ext_Property_Form_Fieldcontainer
{
	use Ext_Property_Form_Labelable;

	public $allowBlank = self::Boolean;
	public $blankText = self::String;
	public $columns = self::Object;
	public $name = self::String;
	public $vertical = self::Boolean;
	
	static public $extend = 'Ext.form.CheckboxGroup';
	static public $xtype = 'checkboxgroup';
}