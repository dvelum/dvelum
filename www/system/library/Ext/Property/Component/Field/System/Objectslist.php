<?php
class Ext_Property_Component_Field_System_Objectslist extends Ext_Property_Panel
{
  	public $name = self::String;  
	public $objectName = self::String;
	public $controllerUrl = self::String;

	public $extraParams = self::Object;
	public $readOnly = self::Boolean;
	
	static public $extend = 'app.objectLink.Panel';
	static public $xtype = 'objectlinkpanel';
}