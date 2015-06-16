<?php

class Ext_Property_Menu_Datepicker extends Ext_Property_Menu
{
	public $ariaLabel = self::String;
    public $hideOnClick = self::Boolean;
    public $pickerId = self::String;

    public static $extend = 'Ext.menu.DatePicker';
    public static $xtype = 'datemenu';
}