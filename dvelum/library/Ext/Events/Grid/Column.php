<?php
class Ext_Events_Grid_Column extends Ext_Events_Container
{
	static protected $_headerClickOptions = array(
		'ct'=>'Ext.grid.header.Container',
		'column'=>'Ext.grid.column.Column',
		'e'=>'Ext.EventObject',
		't'=>'HTMLElement',
		'eOpts'=>'Object'
	);
	static protected $_colSHOptions = array(
		'ct'=>'Ext.grid.header.Container',
		'column'=>'Ext.grid.column.Column',
		'eOpts'=>'Object'
	);

	public $columnhide;
	public $columnmove = array(
		'ct'=>'Ext.grid.header.Container',
		'column'=>'Ext.grid.column.Column',
		'fromIdx'=>'Number',
		'toIdx'=>'Number',
		'eOpts'=>'Object'
	);
	public $columnresize = array(
		'ct'=>'Ext.grid.header.Container',
		'column'=>'Ext.grid.column.Column',
		'width'=>'Number',
		'eOpts'=>'Object'
	);
	public $columnchanged = array(
		'ct'=>'Ext.grid.header.Container',
		'eOpts'=>'Object'
	);
	public $columnshow;
	public $headerclick;
	public $headercontextmenu;
	public $headertriggerclick;
	public $menucreate = array(
		'ct'=>'Ext.grid.header.Container',
		'menu'=>'Ext.menu.Menu',
		'eOpts'=>'Object'
	);
	public $sortchange = array(
		'ct'=>'Ext.grid.header.Container',
		'column'=>'Ext.grid.column.Column',
		'direction'=>'String',
		'eOpts'=>'Object'
	);

	public function _initConfig()
	{
		parent::_initConfig();

		$this->headerclick = static::$_headerClickOptions;
		$this->headercontextmenu = static::$_headerClickOptions;
		$this->headertriggerclick = static::$_headerClickOptions;
		$this->columnhide = static::$_colSHOptions;
		$this->columnshow = static::$_colSHOptions;
	}
}