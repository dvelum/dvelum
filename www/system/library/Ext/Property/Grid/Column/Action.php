<?php
class Ext_Property_Grid_Column_Action extends Ext_Property_Grid_Column{
	public $altText = self::String;
	public $getClass = self::Object;
	public $handler = self::Object;
	public $scope = self::Object;
	public $icon = self::String;
	public $iconCls = self::String;
	public $items = self::Object;
	public $tooltip = self::String;
	public $stopSelection = self::Boolean;
	
	static public $xtype = 'actioncolumn';
}