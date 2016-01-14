<?php
class Ext_Property_Component_Window_System_Crud_Vc extends Ext_Property_Component_Window_System_Crud
{
	public $canPublish = self::Boolean;
	public $hasPreview = self::Boolean;
	public $showToolbar = self::Boolean;
	public $autoPublish = self::Boolean;

	static public $extend = 'app.contentWindow';
	static public $xtype = '';
}