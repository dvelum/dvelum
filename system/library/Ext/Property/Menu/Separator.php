<?php
class Ext_Property_Menu_Separator extends Ext_Property_Menu_Item
{
    /**
     * The CSS class used by the separator item to show the incised line.
     */
    public $separatorCls = self::String;
    
    public static $extend = 'Ext.menu.Separator';
    public static $xtype = 'menuseparator';
}