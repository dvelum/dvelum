<?php
class Ext_Events_Grid_Column_Check extends Ext_Events_Grid_Column
{
	static protected $_checkOptions = array(
		'cmp'=>'Ext.ux.CheckColumn',
		'rowIndex'=>'Number',
		'checked' => 'Boolean',
		'eOpts'=>'Object'
	);

    public $beforecheckchange;
    public $checkchange;

	public function _initConfig()
	{
		parent::_initConfig();

		$this->beforecheckchange = static::$_checkOptions;
		$this->checkchange = static::$_checkOptions;
	}
}