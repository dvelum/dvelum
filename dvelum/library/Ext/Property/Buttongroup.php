<?php
class Ext_Property_Buttongroup extends Ext_Property_Panel
{
	public $columns = self::Number;
	public $defaultButtonUI = self::String;

    static public $extend = 'Ext.container.ButtonGroup';
    static public $xtype = 'buttongroup';
}