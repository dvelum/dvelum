<?php
class Ext_Property_Data_Store_Tree extends Ext_Property_Data_Abstractstore
{
	public $clearOnLoad = self::Boolean;
	public $clearRemovedOnLoad = self::Boolean;
	public $defaultRootId = self::String;
	public $defaultRootProperty = self::String;
	public $folderSort = self::Boolean;
	public $nodeParam = self::String;
	public $root = self::Object;
	
	static public $extend = 'Ext.data.TreeStore';
	static public $xtype = 'store.tree';
}