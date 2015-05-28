<?php
class Ext_Events_Data_Proxystore extends Ext_Events_Data_Abstractstore
{
	static protected $_operOptions = array(
		'store'=>'Ext.data.Store',
		'operation'=>'Ext.data.operation.Operation',
		'eOpts'=>'Object'
	);

	public $beforeload;
	public $beforesync = array(
		'options'=>'Ext.data.operation.Operation',
		'eOpts'=>'Object'
	);
	public $load = array(
		'cmp'=>'Ext.data.Store',
		'records'=>'Ext.data.Model',
		'successful'=>'Boolean',
		'eOpts'=>'Object'
	);
	public $metachange = array(
		'cmp'=>'Ext.data.Store',
		'meta'=>'Object',
		'eOpts'=>'Object'
	);
	public $write;

	public function _initConfig()
	{
		parent::_initConfig();

		$this->beforeload = static::$_operOptions;
		$this->write = static::$_operOptions;
	}
}