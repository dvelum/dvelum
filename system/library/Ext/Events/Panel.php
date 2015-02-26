<?php
class Ext_Events_Panel extends Ext_Events_Component
{
	static protected $_panelOptions = array(
		'p'=>'Ext.panel.Panel',
		'eOpts'=> 'Object'
	);	
	public $bodyresize = array( 
		'p'=>'Ext.panel.Panel', 
		'width'=>'Number',
		'height'=>'Number',
		'eOpts'=>'Object'  
	);
	public $beforeclose;
	public $beforecollapse = array(
		'p'=>'Ext.panel.Panel', 
		'direction'=>'String', 
		'animate'=>'Boolean', 
		'eOpts' =>'Object'  
	);
	public $beforeexpand = array(
		'p'=>'Ext.panel.Panel',
		'animate'=>'Boolean',
		'eOpts'=> 'Object'
	);
	public $collapse;
	public $expand;
	public $iconchange = array(
		 'p' => 'Ext.panel.Panel',
		 'newIconCls'=>'String',
		 'eOpts'=>'Object'
	);
	public $titlechange = array( 
		'p' => 'Ext.panel.Panel',
		'newTitle'=>'String',
		'oldTitle'=>'String',
		'eOpts'=>'Object'
	);
	
	public function _initConfig()
	{
		parent::_initConfig();		
		$this->beforeclose = static::$_panelOptions;
		$this->collapse = static::$_panelOptions;
		$this->expand = static::$_panelOptions;
	}
}