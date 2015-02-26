<?php
class Ext_Property_Toolbar_Textitem extends Ext_Property_Toolbar_Item
{
	public $text = self::String;
	public $renderTpl = self::String;
	
	static public $extend = 'Ext.toolbar.TextItem';
	static public $xtype = 'tbtext';
}