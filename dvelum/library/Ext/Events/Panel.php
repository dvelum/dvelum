<?php
class Ext_Events_Panel extends Ext_Events_Component
{
	static protected $_panelOptions = array(
		'p'=>'Ext.panel.Panel',
		'eOpts'=> 'Object'
	);
	static protected $_floatOptions = array(
		'eOpts'=> 'Object'
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
	public $close = array(
		'panel'=>'Ext.panel.Panel',
		'eOpts'=> 'Object'
	);
	public $collapse;
	public $expand;
	public $float;
	public $glyphchange = array(
		'cmp'=>'Ext.panel.Panel',
		'newGlyph'=>'String',
		'oldGlyph'=>'String',
		'eOpts'=>'Object'
	);
	public $iconchange = array(
		'p' => 'Ext.panel.Panel',
		'newIcon'=>'String',
		'oldIcon'=>'String',
		'eOpts'=>'Object'
	);
	public $iconclschange = array(
		'p' => 'Ext.panel.Panel',
		'newIconCls'=>'String',
		'oldIconCls'=>'String',
		'eOpts'=>'Object'
	);
	public $titlechange = array(
		'p' => 'Ext.panel.Panel',
		'newTitle'=>'String',
		'oldTitle'=>'String',
		'eOpts'=>'Object'
	);
	public $unfloat;
	
	public function _initConfig()
	{
		parent::_initConfig();		
		$this->beforeclose = static::$_panelOptions;
		$this->collapse = static::$_panelOptions;
		$this->expand = static::$_panelOptions;
		$this->float = static::$_floatOptions;
		$this->unfloat = static::$_floatOptions;
	}
}