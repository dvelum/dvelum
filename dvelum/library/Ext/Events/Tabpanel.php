<?php
class Ext_Events_Tabpanel extends Ext_Events_Panel
{
	
	static protected $_tabNCardOCardOptions = array(
		'tabPanel'=>'Ext.tab.Panel',
		'newCard'=>'Ext.Component',
		'oldCard'=>'Ext.Component',
		'eOpts'=>'Object',
	);
	
	public $beforetabchange;
	public $tabchange;
	
	public function _initConfig()
	{
		parent::_initConfig();

		$this->beforetabchange = static::$_tabNCardOCardOptions;
		$this->tabchange = static::$_tabNCardOCardOptions;		
	}

}