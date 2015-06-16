<?php
class Ext_Property_Form_Fieldset extends Ext_Property_Container
{
	public $checkboxName = self::String;
	public $checkboxToggle = self::Boolean;
	public $checkboxUI = self::String;
	public $collapsed = self::Boolean;
	public $collapsible = self::Boolean;
	public $title = self::String;
	public $toggleOnTitleClick = self::Boolean;
	
	static public $extend = 'Ext.form.FieldSet';
	static public $xtype = 'fieldset';
}