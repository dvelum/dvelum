<?php
/**
 * Bsse Field Event abstract (class name based on sencha architecture)
 * @author Kirill A Egorov
 */
abstract class Ext_Events_Form_Field_Base extends Ext_Events_Component
{
	static protected  $_fieldOptions = array(
		 'field'=>'Ext.form.field.Base',  
		 'eOpts'=>'Object'
	);
	
	public $blur;
	public $focus;
	public $specialkey = array( 
		 'field'=>'Ext.form.field.Base',
		 'e'=>'Ext.EventObject',
		 'eOpts'=>'Object'
	);
	
	public $change = array(
			'field'=>'Ext.form.field.Field',
			'newValue'=>'Object',
			'oldValue'=>'Object',
			'eOpts'=>'Object'
	);
			
	public function _initConfig()
	{
		parent::_initConfig();
		$this->blur = static::$_fieldOptions;
		$this->focus = static::$_fieldOptions;
	}
}