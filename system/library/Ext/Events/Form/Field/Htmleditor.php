<?php
class Ext_Events_Form_Field_Htmleditor extends Ext_Events_Component
{
	static protected  $_pushSyncOptions = array(
		'cmp'=>'Ext.form.field.HtmlEditor',
		'html'=>'String',
		'eOpts'=>'Object'
	);
	static protected  $_eOptions = array(
		'eOpts'=>'Object'
	);

	public $beforepush;
	public $beforesync;
	public $editmodechange = array(
		'cmp'=>'Ext.form.field.HtmlEditor',
		'sourceEdit'=>'Boolean',
		'eOpts'=>'Object'
	);
	public $focus;
	public $initialize = array(
		'cmp'=>'Ext.form.field.HtmlEditor',
		'eOpts'=>'Object'
	);
	public $push;
	public $specialkey;
	public $sync;


	public function _initConfig()
	{
		parent::_initConfig();
		$this->beforepush = static::$_pushSyncOptions;
		$this->beforesync = static::$_pushSyncOptions;
		$this->push = static::$_pushSyncOptions;
		$this->sync = static::$_pushSyncOptions;
		$this->focus = static::$_eOptions;
		$this->specialkey = static::$_eOptions;
	}
}