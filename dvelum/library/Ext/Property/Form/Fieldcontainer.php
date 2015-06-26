<?php
class Ext_Property_Form_Fieldcontainer extends Ext_Property_Container
{
	use Ext_Property_Form_Labelable;

	public $combineErrors = self::Boolean;
	public $combineLabels = self::Boolean;
	public $labelConnector = self::String;

	static public $extend = 'Ext.form.FieldContainer';
	static public $xtype = 'fieldcontainer';
}