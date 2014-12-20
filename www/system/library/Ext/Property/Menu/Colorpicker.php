<?php

class Ext_Property_Menu_Colorpicker extends Ext_Property_Menu
{

    /**
     * False to continue showing the menu after a color is selected.
     */
    public $hideOnClick = self::Boolean;

    /**
     * An id to assign to the underlying color picker.
     */
    public $pickerId = self::String;

    public static $extend = 'Ext.menu.ColorPicker';
    public static $xtype = 'colormenu';
}