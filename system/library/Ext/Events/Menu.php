<?php
class Ext_Events_Menu extends Ext_Events_Panel
{
	protected static $_menuClickOptions = array(
		'menu' => 'Ext.menu.Menu',
		'item' => ' Ext.Component',
		'e' => 'Ext.EventObject',
		'eOpts' => 'Object'
	);
	protected static $_menuMouseMoveOptions = array(
		'menu' => 'Ext.menu.Menu',
		'e' => 'Ext.EventObject',
		'eOpts' => 'Object'
	);

	public $click;
	public $mouseenter;
	public $mouseleave;
	public $mouseover;

	public function _initConfig()
	{
		parent::_initConfig();

		$this->click = static::$_menuClickOptions;
		$this->mouseover = static::$_menuClickOptions;
		$this->mouseenter = static::$_menuMouseMoveOptions;
		$this->mouseleave = static::$_menuMouseMoveOptions;
	}
}