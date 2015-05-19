<?php
class Ext_Property_View_Table extends Ext_Property_View
{
	public $enableTextSelection = self::Boolean;
	public $firstCls = self::String;
	public $lastCls = self::String;
	public $markDirty = self::Boolean;
	public $stripeRows = self::Boolean;

	static public $extend = 'Ext.view.Table';
}