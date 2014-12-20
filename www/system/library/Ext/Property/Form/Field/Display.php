<?php
class Ext_Property_Form_Field_Display extends Ext_Property_Form_Field_Base
{
	public $fieldCls = self::String;
	public $htmlEncode  = self::Boolean;
	
	static public $extend = 'Ext.form.field.Display';
	static public $xtype = 'displayfield';
}