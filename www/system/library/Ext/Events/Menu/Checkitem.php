<?php


class Ext_Events_Menu_Checkitem extends Ext_Events_Menu_Item
{
    /**
     * Fires before a change event. Return false to cancel.
     */
    public $beforecheckchange = array(
        'cmp' => 'Ext.menu.CheckItem',
        'checked' => 'Boolean',
        'eOpts' => 'Object'
    );
    /**
     * Fires after a change event.
     */
    public $checkchange = array(
    		'cmp' => 'Ext.menu.CheckItem',
    		'checked' => 'Boolean',
    		'eOpts' => 'Object'
    );
}