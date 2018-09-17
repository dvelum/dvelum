<?php
class Ext_Property_Tabpanel extends Ext_Property_Panel
{
	public $activeTab = self::Object;
	public $deferredRender = self::Boolean;
	public $itemCls = self::String;
	public $maxTabWidth = self::Number;
	public $minTabWidth = self::Number;
	public $plain = self::Boolean;
	public $removePanelHeader = self::Boolean;
	public $tabBar = self::Object;
	public $tabBarHeaderPosition = self::Number;
	public $tabPosition = self::String;
	public $tabRotation = self::Number;
	public $tabStretchMax = self::Boolean;
	
	static public $extend = 'Ext.tab.Panel';
	static public $xtype = 'tabpanel';
}