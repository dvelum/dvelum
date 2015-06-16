<?php


class Ext_Events_Menu_Colorpicker extends Ext_Events_Menu
{
    public $click = array(
        'eOpts'=>'Object'
    );
    /**
     * Fires when a color is selected
     */
    public $select = array(
        'cmp' => 'Ext.picker.Color',
        'color' => 'String',
        'eOpts' => 'Object'
    );

}