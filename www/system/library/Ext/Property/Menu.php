<?php
class Ext_Property_Menu extends Ext_Property_Panel
{
    /**
     * True to allow multiple menus to be displayed at the same time.
     */
    public $allowOtherMenus = self::Boolean;

    /**
     * True to enable keyboard navigation for controlling the menu.
     */
    public $enableKeyNav = self::Boolean;

    /**
     * A Menu configured as floating: true (the default) will be rendered as an absolutely positioned, floating Component.
     */
    public $floating = self::Boolean;

    /**
     * True to initially render the Menu as hidden, requiring to be shown manually.
     */
    public $hidden = self::Boolean;

    /**
     * A String which specifies how this Component's encapsulating DOM element will be hidden.
     */
    public $hideMode = self::String;

    /**
     * True to ignore clicks on any item in this menu that is a parent item (displays a submenu) so that the submenu is not
     */
    public $ignoreParentClicks = self::Boolean;

    /**
     * The minimum width of the Menu.
     */
    public $minWidth = self::Numeric;

    /**
     * True to remove the incised line down the left side of the menu and to not indent general Component items.
     */
    public $plain = self::Boolean;

    /**
     * True to show the icon separator.
     */
    public $showSeparator = self::Boolean;

    public static $extend = 'Ext.menu.Menu';
    public static $xtype = 'menu';
}