<?php
class Ext_Events_Component extends Ext_Events
{
	static protected $_componentOptions = array(
		'cmp'=>'Ext.Component', 
		'eOpts'=>'Object'
	);
	static protected $_evOptions = array(
		'cmp'=>'Ext.Component',
		'e'=>'Object',
		'eOpts'=>'Object'
	);
	static protected $_statefulStateOptions = array(
		'stateful'=>'Ext.state.Stateful', 
		'state'=>'Object', 
		'eOpts'=>'Object'
	);
		
	public $activate;
	public $added = array(
		'cmp'=>'Ext.Component',
		'container'=>'Ext.container.Container', 
		'pos'=>'Number',
		'eOpts'=>'Object'
	);	
    public $afterrender;
	public $beforeactivate;
	public $beforedeactivate;
	public $beforedestroy;
	public $beforehide;
	public $beforerender;
	public $beforeshow;
	public $beforestaterestore;
	public $beforestatesave;
	public $blur;
	public $boxready = array(
		'cmp'=>'Ext.Component',
		'width'=>'Number',
		'height'=>'Number',
		'eOpts'=>'Object'
	);
	public $deactivate;
	public $destroy;
	public $disable;
	public $enable;
	public $focus;
	public $hide;
	public $move = array(
		'cmp'=>'Ext.Component',
		'x'=>'Number',
		'y'=>'Number',
		'eOpts'=>'Object'
	);
	public $removed = array(
		'cmp'=>'Ext.Component',
		'ownerCt'=>'Ext.container.Container',
		'eOpts'=>'Object'
	);
	public $render;
	public $resize = array(
		'cmp'=>'Ext.Component', 
		'adjWidth'=>'Number',
		'adjHeight'=>'Number', 
		'eOpts'=>'Object'
	);
	public $show;
	public $staterestore;
	public $statesave;
	
	public function _initConfig()
	{
		parent::_initConfig();	
		
		$this->activate = static::$_componentOptions;	
		$this->afterrender = static::$_componentOptions;
		$this->beforeactivate = static::$_componentOptions;
		$this->beforedeactivate  = static::$_componentOptions;
		$this->beforedestroy = static::$_componentOptions;
		$this->beforehide = static::$_componentOptions;
		$this->beforerender = static::$_componentOptions;
		$this->beforeshow = static::$_componentOptions;
		$this->beforestaterestore = static::$_statefulStateOptions;
		$this->beforestatesave = static::$_statefulStateOptions;
		$this->blur = static::$_evOptions;
		$this->deactivate = static::$_componentOptions;
		$this->destroy = static::$_componentOptions;
		$this->disable = static::$_componentOptions;
		$this->enable = static::$_componentOptions;
		$this->focus = static::$_evOptions;
		$this->hide = static::$_componentOptions;
		$this->render = static::$_componentOptions;
		$this->show = static::$_componentOptions;
		$this->staterestore = static::$_statefulStateOptions;
		$this->statesave = static::$_statefulStateOptions;
	}
	
}