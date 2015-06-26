<?php
class Ext_Events_Form_Fieldset extends Ext_Events_Container
{
	protected static $_fieldsetOptions = array(
		'cmp' => 'Ext.form.FieldSet',
		'eOpts' => 'Object'
	);

	public $beforecollapse;
	public $beforeexpand;
	public $collapse;
	public $expand;

	public function _initConfig()
	{
		parent::_initConfig();

		$this->beforecollapse = static::$_fieldsetOptions;
		$this->beforeexpand = static::$_fieldsetOptions;
		$this->collapse = static::$_fieldsetOptions;
		$this->expand = static::$_fieldsetOptions;
	}

}