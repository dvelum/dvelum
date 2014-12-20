<?php
class Ext_Property_Component_Field_System_Medialibitem extends Ext_Property_Form_Field_Display
{
	public $resourceType = self::String;
	
	static public $extend = 'app.medialib.ItemField';
	static public $xtype = 'medialibitemfield';
}