<?php
class Ext_Property_Form_Radiogroup extends Ext_Property_Form_Checkboxgroup
{
	public $items = self::Object;
	public $allowBlank = self::Boolean;
	public $blankText = self::String;
	
	static public $extend = 'Ext.form.RadioGroup';
	static public $xtype = 'radiogroup';
}