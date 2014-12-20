<?php
class Ext_Property_Tabpanel extends Ext_Property_Panel
{
	public $tabPosition = self::String;
	public $actibeItem = self::String;
	/*
     * {String/Number/Ext.Component}
     */
	public $activeTab = self::Object;
	public $tabBar = self::Object;
	public $layout = self::Object;
	public $removePanelHeader = self::Boolean;
	public $plain = self::Boolean;
	public $itemCls = self::String;
	public $minTabWidth = self::Numeric;
	public $maxTabWidth = self::Numeric;
	public $deferredRender = self::Boolean;
	
	static public $extend = 'Ext.tab.Panel';
	static public $xtype = 'tabpanel';
}