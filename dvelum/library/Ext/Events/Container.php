<?php
class Ext_Events_Container extends Ext_Events_Component
{
	static protected $_addOptions = array(
		'container' => 'Ext.container.Container',
		'component' => 'Ext.Component',
		'index' => 'Number',
		'eOpts' => 'Oject'
	);
	static protected $_removeOptions = array(
		'container' => 'Ext.container.Container',
		'component' => 'Ext.Component',
		'eOpts' => 'Object'
	);

	public $add;
	public $afterlayout = array(
		'container' => 'Ext.container.Container',
		'layout' => 'Ext.layout.container.Container',
		'eOpts' => 'Object'
	);
	public $beforeadd;
	public $beforeremove;
	public $childmove = array(
		'container' => 'Ext.container.Container',
		'component' => 'Ext.Component',
		'prevIndex' => 'Number',
		'newIndex' => 'Number',
		'eOpts' => 'Object'
	);
	public $remove;

	public function _initConfig()
	{
		parent::_initConfig();

		$this->add = static::$_addOptions;
		$this->beforeadd = static::$_addOptions;
		$this->remove = static::$_removeOptions;
		$this->beforeremove = static::$_removeOptions;
	}
}