<?php
class Ext_Property_Form extends Ext_Property_Panel
{
	public $fieldDefaults = self::Object;
    public $pollForChanges = self :: Boolean;
    public $pollInterval = self :: Number;
    public $layout = self :: String;
    public $buttons = self::Object;
     
    static public $extend = 'Ext.form.Panel';
	static public $xtype = 'form';
}