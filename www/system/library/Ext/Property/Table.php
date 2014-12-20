<?php
class Ext_Property_Table extends Ext_Property_Panel{ 
	public $hasView = self::Boolean;
    public $viewType = self::String;
    public $viewConfig = self::Object;
    public $view = self::Object;
    public $selType = self::String;
    public $selModel = self::Object;
    public $multiSelect = self::Boolean;
    public $simpleSelect = self::Boolean;
    public $store =  self::Object;
    public $scrollDelta = self::Numeric;
    public $scroll = self::String;
    public $columns = self::Object;
    public $forceFit = self::Boolean;
    public $features = self::Object;
    public $hideHeaders = self::Boolean;
    public $deferRowRender = self::Boolean;
    public $sortableColumns = self::Boolean;
    public $enableLocking = self::Boolean;
    public $verticalScrollDock = self::String;
    public $verticalScrollerType = self::String;
    public $horizontalScrollerPresentCls = self::String;
    public $verticalScrollerPresentCls = self::String;
    public $invalidateScrollerOnRefresh = self::Boolean;
    public $enableColumnMove= self::Boolean;
    public $enableColumnResize= self::Boolean;
    public $enableColumnHide= self::Boolean;
	
}