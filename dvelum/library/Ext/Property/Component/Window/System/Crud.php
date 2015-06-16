<?php
class Ext_Property_Component_Window_System_Crud extends Ext_Property_Window
{
	public $controllerUrl = self::String;
	public $objectName = self::String;
	public $canEdit = self::Boolean;
	public $canDelete = self::Boolean;
	public $fieldDefaults = self::Object;
	public $hideEastPanel = self::Boolean;
	public $eastPanelCollapsed = self::Boolean;
	public $useTabs = self::Boolean;
	public $primaryKey = self::String;
	public $showToolbar = self::Boolean;
	public $extraParams = self::Object;
	
	static public $extend = 'app.editWindow';
	static public $xtype = '';
}