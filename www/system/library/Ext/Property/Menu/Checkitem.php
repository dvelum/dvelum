<?php
class Ext_Property_Menu_Checkitem extends Ext_Property_Menu_Item
{
    /**
     * True to prevent the checked item from being toggled.
     */
    public $checkChangeDisabled = self::Boolean;

    /**
     * Alternative for the checkchange event.
     */
    public $checkHandler = self::Object;

    /**
     * True to render the menuitem initially checked.
     */
    public $checked = self::Boolean;

    /**
     * The CSS class used by cls to show the checked state.
     */
    public $checkedCls = self::String;

    /**
     * Name of a radio group that the item belongs.
     */
    public $group = self::String;

    /**
     * The CSS class applied to this item's icon image to denote being a part of a radio group.
     */
    public $groupCls = self::String;

    /**
     * Whether to not to hide the owning menu when this item is clicked.
     */
    public $hideOnClick = self::Boolean;

    /**
     * Scope for the checkHandler callback.
     */
    public $scope = self::Object;

    /**
     * The CSS class used by cls to show the unchecked state.
     */
    public $uncheckedCls = self::String;

    public static $extend = 'Ext.menu.CheckItem';
    public static $xtype = 'menucheckitem';
}