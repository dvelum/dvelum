<?php
class Ext_Property_Button_Split extends Ext_Property_Button
{
	public $arrowHandler = self::Object;
    public $arrowTooltip = self::String;

    static public $extend = 'Ext.button.Split';
    static public $xtype = 'splitbutton';
}
