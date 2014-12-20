<?php
class Ext_Property_View_Table extends Ext_Property_View_View
{
	public $baseCls = self::String;
	public $itemSelector = self::String;
	public $cellSelector = self::String;
	public $selectedItemCls = self::String;
	public $selectedCellCls = self::String;
	public $focusedItemCls = self::String;
	public $overItemCls = self::String;
	public $altRowCls = self::String;
	public $rowClsRe = self::Object;
	public $cellRe = self::Object;
	public $trackOver = self::Boolean;
	public $getRowClass = self::Object;
}