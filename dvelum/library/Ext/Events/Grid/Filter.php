<?php
class Ext_Events_Grid_Filter extends Ext_Events
{
	static protected $_filterOptions = array(
		'cmp' => 'Ext.grid.filters.Filters',
		'eOpts' => 'Object'
	);

	public $activate;
	public $deactivate;
	public $update;

	public function _initConfig()
	{
		parent::_initConfig();

		$this->activate = static::$_filterOptions;
		$this->deactivate = static::$_filterOptions;
		$this->update = static::$_filterOptions;
	}
}