<?php
class Ext_Property_Form_Fieldset extends Ext_Property_Container
{
	public $title = self::String;
	public $checkboxToggle = self::Boolean;
	public $checkboxName = self::String;
	public $collapsible = self::Boolean;
	public $collapsed = self::Boolean;
	public $legend = self::Object;
	public $baseCls = self::String;
	public $layout = self::String;
	
	static public $extend = 'Ext.form.FieldSet';
	static public $xtype = 'fieldset';
}