<?php
class Ext_Property_Form_Field_Display extends Ext_Property_Form_Field_Base
{
	public $htmlEncode = self::Boolean;
	public $renderer = self::Object;
	public $scope = self::Object;
	public $submitValue = self::Boolean;
	public $validateOnChange = self::Boolean;
	public $value = self::String;

	static public $extend = 'Ext.form.field.Display';
	static public $xtype = 'displayfield';
}