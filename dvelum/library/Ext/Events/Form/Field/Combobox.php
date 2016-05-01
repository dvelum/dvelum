<?php
class Ext_Events_Form_Field_Combobox extends Ext_Events_Form_Field_Picker
{
	static protected $_comboRecordIndexOptions = array(
		'combo'=>'Ext.form.field.ComboBox',
		'record'=>'Ext.data.Record',
		'index'=>'Number',
		'eOpts'=>'Object'
	);
	public $beforedeselect;
	public $beforequery = array(
		'queryPlan'=>'Object',
		'eOpts'=>'Object'
	);
	public $beforeselect;
	public $select = array(
		 'combo'=>'Ext.form.field.ComboBox',
		 'records'=>'Ext.data.Model',
		 'eOpts'=>'Object'
	);
	public function _initConfig()
	{
		parent::_initConfig();
		$this->beforedeselect = static::$_comboRecordIndexOptions;
		$this->beforeselect = static::$_comboRecordIndexOptions;
	}
}