<?php
class Ext_Events_Window extends Ext_Events_Panel
{
	
	static protected $_windowOptions = array(
		'cmp'=>'Ext.window.Window',
		'eOpts'=>'Object'
	);
	
	public $activate;
	public $deactivate;
	public $maximize;
	public $minimize;
	public $resize = array( 
		'cmp'=>'Ext.window.Window',
		'width'=>'Number', 
		'height'=>'Number', 
		'eOpts'=>'Object'
	);
	public $restore;

		
	public function _initConfig()
	{
		parent::_initConfig();
		$this->activate = static::$_windowOptions;
		$this->deactivate = static::$_windowOptions;
		$this->maximize = static::$_windowOptions;
		$this->minimize = static::$_windowOptions;
		$this->restore = static::$_windowOptions;	
	}
}