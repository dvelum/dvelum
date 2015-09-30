<?php
class Ext_Property_Component_Field_System_Dictionary extends Ext_Property_Form_Field_Combobox
{
	public $dictionary = self::String;
	public $showAll = self::Boolean;
	//public $showAllText = self::String;
	public $showReset = self::Boolean;
	
	static public $extend = 'Ext.form.field.ComboBox';
	static public $xtype = 'combobox';
}