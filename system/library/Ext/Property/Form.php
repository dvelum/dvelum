<?php
class Ext_Property_Form extends Ext_Property_Panel
{
	public $api = self::Object;
	public $baseParams = self::Object;
	public $errorReader = self::Object;
	public $jsonSubmit = self::Boolean;
	public $method = self::String;
	public $paramOrder = self::String;
	public $paramsAsHash = self::Boolean;
	public $pollForChanges = self::Boolean;
	public $pollInterval = self::Number;
	public $reader = self::Object;
	public $standardSubmit = self::Boolean;
	public $timeout = self::Number;
	public $trackResetOnLoad = self::Boolean;
	public $url = self::String;
	public $waitMsgTarget = self::String;
	public $waitTitle = self::String;
     
    static public $extend = 'Ext.form.Panel';
	static public $xtype = 'form';
}