<?php
class Ext_Property_Menu_Checkitem extends Ext_Property_Menu_Item
{
	public $ariaRole = self::String;
	public $checkChangeDisabled = self::Boolean;
	public $checkHandler = self::Object;
	public $checked = self::Boolean;
	public $checkedCls = self::String;
	public $group = self::String;
	public $groupCls = self::String;
	public $scope = self::Object;
	public $uncheckedCls = self::String;

    public static $extend = 'Ext.menu.CheckItem';
    public static $xtype = 'menucheckitem';
}