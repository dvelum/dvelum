<?php
class Ext_Property_Table extends Ext_Property_Panel {
	public $store = self::Object;
	public $allowDeselect = self::Boolean;
	public $autoLoad = self::Boolean;
	public $bufferedRenderer = self::Boolean;
	public $columnLines = self::Boolean;
	public $columns = self::Object;
	public $deferRowRender = self::Boolean;
	public $disableSelection = self::Boolean;
	public $emptyText = self::String;
	public $enableColumnHide = self::Boolean;
	public $enableColumnMove = self::Boolean;
	public $enableColumnResize = self::Boolean;
	public $enableLocking = self::Boolean;
	public $features = self::Object;
	public $forceFit = self::Boolean;
	public $hideHeaders = self::Boolean;
	public $leadingBufferZone = self::Number;
	public $multiColumnSort = self::Boolean;
	public $numFromEdge = self::Number;
	public $reserveScrollbar = self::Boolean;
	public $rowLines = self::Boolean;
	public $sealedColumns = self::Boolean;
	public $selModel = self::Object;
	public $selection = self::Object;
	public $sortableColumns = self::Boolean;
	public $trailingBufferZone = self::Number;
	public $view = self::Object;
	public $viewConfig = self::Object;
	public $viewType = self::String;

	static public $extend = 'Ext.panel.Table';
	static public $xtype = 'tablepanel';
}