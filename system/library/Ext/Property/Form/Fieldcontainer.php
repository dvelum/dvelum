<?php
class Ext_Property_Form_Fieldcontainer extends Ext_Property_Container
{
	public $combineErrors = self::Boolean;
	public $combineLabels = self::Boolean;
	public $labelConnector = self::String;
	public $fieldLabel = self::String;
	public $labelWidth = self::Numeric;
	public $layout = self::String;
	
	static public $extend = 'Ext.form.FieldContainer';
	static public $xtype = 'fieldcontainer';
}