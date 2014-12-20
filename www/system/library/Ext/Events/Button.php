<?php
class Ext_Events_Button extends Ext_Events_Component
{
	static protected $_buttonEventOptions = array(
		'btn'=>'Ext.button.Button',  
		'menu'=>'Ext.menu.Menu', 
		'e'=>'Event', 
		'eOpts'=>'Object' 
	); 
	
	public $click;
	public $menuhide = array(
		'btn'=>'Ext.button.Button',  
		'menu'=>'Ext.menu.Menu', 
		'eOpts'=>'Object' 
	);
	public $menushow = array(
		'btn'=>'Ext.button.Button',  
		'menu'=>'Ext.menu.Menu', 
		'eOpts'=>'Object' 
	);
	public $menutriggerout = array(
		'btn'=>'Ext.button.Button',  
		'menu'=>'Ext.menu.Menu', 
		'e'=>'Event', 
		'eOpts'=>'Object' 
	);
	public $menutriggerover = array(
		'btn'=>'Ext.button.Button',  
		'menu'=>'Ext.menu.Menu', 
		'e'=>'Event', 
		'eOpts'=>'Object' 
	);
	public $mouseout;
	public $mouseover;
	
	public $handler = array(
		'button' => 'Ext.button.Button',
		'e' => 'Ext.EventObject'
	);
	
	public $toggle = array( 
		'btn'=>'Ext.button.Button', 
		'pressed'=>'Boolean', 
		'eOpts'=>'Object' 
	);
	
	public function _initConfig()
	{
		parent::_initConfig();
		$this->click = static::$_buttonEventOptions;
		$this->mouseout = static::$_buttonEventOptions;
		$this->mouseover = static::$_buttonEventOptions;	
	}
}