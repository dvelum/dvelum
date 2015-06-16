<?php
abstract class Ext_Events_Form_Field_Spinner extends Ext_Events_Form_Field_Text
{
	static protected $_spinnerDirectionOptions = array(
		'cmp'=>'Ext.form.field.Spinner',
		'eOpts'=>'Object'
	);

	public $spin = array( 
		'cmp'=>'Ext.form.field.Spinner',
		'direction'=>'String', 
		'eOpts'=>'Object'
	);

	public $spindown;
	public $spinup;
	
	public function _initConfig()
	{
		parent::_initConfig();
		$this->spindown = static::$_spinnerDirectionOptions;
		$this->spinup = static::$_spinnerDirectionOptions;
	}
}