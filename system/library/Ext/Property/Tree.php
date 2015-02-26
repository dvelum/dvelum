<?php
class Ext_Property_Tree extends Ext_Property_Table
{ 
	public $store = self::Object;
	public $animate = self::Boolean;
	public $displayField = self::String;
	public $folderSort = self::Boolean;
	public $hideHeaders = self::Boolean;
	public $lines = self::Boolean;
	public $root = self::Object;
	public $rootVisible = self::Boolean;
	public $singleExpand = self::Boolean;
	public $useArrows = self::Boolean;
	
	// abstract panel
	public $baseCls = self::String;
	public $bodyBorder  = self::Boolean;
	public $bodyCls = self::String;
	public $bodyPadding = self::Number;
	public $bodyStyle= self::String;
	public $border = self::Number;
	public $componentLayout  = self::String;
	public $dockedItems = self::Object;
	public $shrinkWrapDock = self::Number;
	
	static public $extend = 'Ext.tree.Panel';
	static public $xtype = 'treepanel';
}