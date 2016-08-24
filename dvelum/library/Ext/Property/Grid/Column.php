<?php
class Ext_Property_Grid_Column extends Ext_Property_Container
{
	public $align = self::String;
	public $cellWrap = self::Boolean;
	public $columns = self::Object;
	public $dataIndex = self::String;
	public $editRenderer = self::Object;
	public $editor = self::Object;
	public $emptyCellText = self::String;
	public $enableFocusableContainer = self::Boolean;
	public $formatter = self::String;
	public $filter = self::Object;
	public $groupable = self::Boolean;
	public $headerWrap = self::Boolean;
	public $hideable = self::Boolean;
	public $lockable = self::Boolean;
	public $locked = self::Boolean;
	public $menuDisabled = self::Boolean;
	public $menuText = self::String;
	public $producesHTML = self::Boolean;
	public $renderer = self::Object;
	public $scope = self::Object;
	public $sortable = self::Boolean;
	public $summaryRenderer = self::Object;
	public $tdCls = self::String;
	public $text = self::String;
	public $tooltip = self::String;
	public $tooltipType = self::String;
	public $triggerVisible = self::Boolean;
	public $updater = self::Object;
	public $variableRowHeight = self::Boolean;

    public $summaryType = self::String;

	// dvelum designer property
	public $projectColId = self::String;

	static public $extend = 'Ext.grid.column.Column';
	static public $xtype = 'gridcolumn';
}