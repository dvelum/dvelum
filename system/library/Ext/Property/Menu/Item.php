<?php

class Ext_Property_Menu_Item extends Ext_Property_Component
{

    /**
     * The CSS class added to the menu item when the item is activated (focused/mouseover).
     */
    public $activeCls = self::String;

    /**
     * Whether or not this menu item can be activated when focused/mouseovered.
     */
    public $canActivate = self::Boolean;

    /**
     * The delay in milliseconds to wait before hiding the menu after clicking the menu item.
     */
    public $clickHideDelay = self::Numeric;

    /**
     * Whether or not to destroy any associated sub-menu when this item is destroyed.
     */
    public $destroyMenu = self::Boolean;

    /**
     * The CSS class added to the menu item when the item is disabled.
     */
    public $disabledCls = self::String;

    /**
     * A numeric unicode character code to use as the icon for this item.
     */
    public $glyph = self::String;

    /**
     * A function called when the menu item is clicked (can be used instead of click event).
     */
    public $handler = self::Object;

    /**
     * Whether to not to hide the owning menu when this item is clicked.
     */
    public $hideOnClick = self::Boolean;

    /**
     * The href attribute to use for the underlying anchor link.
     */
    public $href = self::String;

    /**
     * The target attribute to use for the underlying anchor link.
     */
    public $hrefTarget = self::String;

    /**
     * The path to an icon to display in this item.
     */
    public $icon = self::String;

    /**
     * A CSS class that specifies a background-image to use as the icon for this item.
     */
    public $iconCls = self::String;

    /**
     * Either an instance of Ext.menu.Menu or a config object for an Ext.menu.Menu which will act as a sub-menu to this item.
     */
    public $menu = self::Object;

    /**
     * The default Ext.util.Positionable.getAlignToXY anchor position value for this item's sub-menu relative to this item's...
     */
    public $menuAlign = self::String;

    /**
     * The delay in milliseconds before this item's sub-menu expands after this item is moused over.
     */
    public $menuExpandDelay = self::Numeric;

    /**
     * The delay in milliseconds before this item's sub-menu hides after this item is moused out.
     */
    public $menuHideDelay = self::Numeric;

    /**
     * Whether or not this item is plain text/html with no icon or visual activation.
     */
    public $plain = self::Boolean;

    /**
     * The text/html to display in this item.
     */
    public $text = self::String;

    /**
     * The tooltip for the button - can be a string to be used as innerHTML (html tags are accepted) or QuickTips config obj...
     */
    public $tooltip = self::String;

    /**
     * The type of tooltip to use.
     */
    public $tooltipType = self::String;
    
    public static $extend = 'Ext.menu.Item';
    public static $xtype = 'menuitem';
}