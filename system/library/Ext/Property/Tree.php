<?php
class Ext_Property_Tree extends Ext_Property_Table
{
	public $animate = self::Boolean;
	public $displayField = self::String;
	public $folderSort = self::Boolean;
	public $lines = self::Boolean;
	public $root = self::Object;
	public $rootVisible = self::Boolean;
	public $singleExpand = self::Boolean;
	public $useArrows = self::Boolean;
	
	static public $extend = 'Ext.tree.Panel';
	static public $xtype = 'treepanel';
}