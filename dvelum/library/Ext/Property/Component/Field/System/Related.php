<?php
class Ext_Property_Component_Field_System_Related extends Ext_Property_Panel
{
	public $fieldName = self::String;
	public $rootProperty = self::String;
	public $controllerUrl = self::String;
	public $deleteColumn = self::Boolean;
	public $sortColumn = self::Boolean;

	static public $extend = 'app.relatedGridPanel';
	static public $xtype = 'relatedgridpanel';
}