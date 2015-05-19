<?php
class Ext_Property_Data_Store_Tree extends Ext_Property_Data_Store
{
	public $clearOnLoad = self::Boolean;
	public $defaultRootId = self::String;
	public $defaultRootProperty = self::String;
	public $defaultRootText = self::String;
	public $folderSort = self::Boolean;
	public $lazyFill = self::Boolean;
	public $node = self::Object;
	public $nodeParam = self::String;
	public $parentIdProperty = self::String;
	public $recursive = self::Boolean;
	public $root = self::Object;
	public $rootVisible = self::Boolean;

	static public $extend = 'Ext.data.TreeStore';
}