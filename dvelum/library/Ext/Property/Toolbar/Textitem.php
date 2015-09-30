<?php
class Ext_Property_Toolbar_Textitem extends Ext_Property_Toolbar_Item
{
	public $baseCls = self::String;
	public $text = self::String;

	static public $extend = 'Ext.toolbar.TextItem';
	static public $xtype = 'tbtext';
}