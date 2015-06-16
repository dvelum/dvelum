<?php
abstract class Ext_Events_Form_Field_Picker extends Ext_Events_Form_Field_Text
{
	static protected  $_fieldOptions = array(
		 'cmp'=>'Ext.form.field.Picker',
		 'eOpts'=>'Object'
	);
		
	public $collapse;
	public $expand;
	public $select = array( 
		'cmp'=>'Ext.form.field.Picker',
		'value'=>'Object', 
		'eOpts'=>'Object'
	);
	
	public function _initConfig()
	{
		parent::_initConfig();
		$this->collapse = static::$_fieldOptions;
		$this->expand = static::$_fieldOptions;
	}
}