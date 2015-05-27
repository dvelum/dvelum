<?php
class Ext_Events_Form_Field_Text extends Ext_Events_Form_Field_Base
{
	static protected $_fieldEventOptions = array(
	 	 'cmp'=>'Ext.form.field.Text',
	 	 'e'=>'Ext.EventObject', 
	 	 'eOpts'=>'Object' 
	);
	
	public $autosize = array( 
		'cmp'=>'Ext.form.field.Text',
		'width'=>'Number', 
		'eOpts'=>'Object' 
	);
	
	public $keydown;
	public $keypress;
	public $keyup;
	
	public function _initConfig()
	{
		parent::_initConfig();
		
		$this->keydown = static::$_fieldEventOptions;
		$this->keypress = static::$_fieldEventOptions;
		$this->keyup = static::$_fieldEventOptions;	
	}
}