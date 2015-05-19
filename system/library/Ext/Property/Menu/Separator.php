<?php
class Ext_Property_Menu_Separator extends Ext_Property_Menu_Item
{
	public $canActivate = self::Boolean;
	public $separatorCls = self::String;
    
    public static $extend = 'Ext.menu.Separator';
    public static $xtype = 'menuseparator';
}