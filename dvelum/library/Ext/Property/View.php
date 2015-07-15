<?php
class Ext_Property_View extends Ext_Property_Component
{
	public $itemSelector = self::String;
	public $blockRefresh = self::Boolean;
	public $deferEmptyText = self::Boolean;
	public $deferInitialRefresh = self::Boolean;
	public $disableSelection = self::Boolean;
	public $emptyText = self::String;
	public $itemCls = self::String;
	public $itemTpl = self::Object;
	public $loadMask = self::Boolean;
	public $loadingCls = self::String;
	public $loadingHeight = self::Number;
	public $loadingText = self::String;
	public $loadingUseMsg = self::Boolean;
	public $navigationModel = self::Object;
	public $overItemCls = self::String;
	public $preserveScrollOnRefresh = self::Boolean;
	public $selectedItemCls = self::String;
	public $selection = self::Object;
	public $selectionModel = self::Object;
	public $store = self::Object;
	public $throttledUpdate = self::Boolean;
	public $trackOver = self::Boolean;
	public $updateDelay = self::Number;

	static public $extend = 'Ext.view.View';
}