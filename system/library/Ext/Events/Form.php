<?php
class Ext_Events_Form extends Ext_Events_Panel
{
	protected static $_actionOptions = array(
		'cmp' => 'Ext.form.Basic',
		'action' => 'Ext.form.action.Action',
		'eOpts' => 'Object'
	);

	public $actoincomplete;
	public $actionfailed;
	public $beforeaction;
	public $dirtychange = array(
		'cmp' => 'Ext.form.Panel',
		'dirty' => 'Boolean',
		'eOpts' => 'Object'
	);
	public $validitychange = array(
		'cmp' => 'Ext.form.Panel',
		'valid' => 'Boolean',
		'eOpts' => 'Object'
	);

	public function _initConfig()
	{
		parent::_initConfig();

		$this->actoincomplete = static::$_actionOptions;
		$this->actoinfailed = static::$_actionOptions;
		$this->beforeaction = static::$_actionOptions;
	}
}