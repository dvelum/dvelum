<?php

class Ext_Property_Menu_Colorpicker extends Ext_Property_Menu
{
    public $hideOnClick = self::Boolean;
    public $pickerId = self::String;

    public static $extend = 'Ext.menu.ColorPicker';
    public static $xtype = 'colormenu';
}