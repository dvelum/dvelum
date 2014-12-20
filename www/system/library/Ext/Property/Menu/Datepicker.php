<?php

class Ext_Property_Menu_Datepicker extends Ext_Property_Menu
{

    /**
     * False to continue showing the menu after a date is selected.
     */
    public $hideOnClick = self::Boolean;

    /**
     * An id to assign to the underlying date picker.
     */
    public $pickerId = self::String;

    public static $extend = 'Ext.menu.DatePicker';
    public static $xtype = 'datemenu';
}