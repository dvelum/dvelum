<?php

class Ext_Events_Menu_Item extends Ext_Events_Component
{

    /**
     * Fires when this item is activated
     */
    public $activate = array(
        'item' => 'Ext.menu.Item',
        'eOpts' => 'Object'
    );

    /**
     * Fires when this item is clicked
     */
    public $click = array(
        'item' => 'Ext.menu.Item',
        'e' => 'Ext.event.Event',
        'eOpts' => 'Object'
    );

    /**
     * Fires when this tiem is deactivated
     */
    public $deactivate = array(
        'item' => 'Ext.menu.Item',
        'eOpts' => 'Object'
    );

    /**
     * Fired when the item's icon is changed by the setIcon or setIconCls methods.
     */
    public $iconchange = array(
        'cmp' => 'Ext.menu.Item',
        'oldIcon' => 'String',
        'newIcon' => 'String',
        'eOpts' => 'Object'
    );

    /**
     * Fired when the item's text is changed by the setText method.
     */
    public $textchange = array(
        'cmp' => 'Ext.menu.Item',
        'oldText' => 'String',
        'newText' => 'String',
        'eOpts' => 'Object'
    );
}