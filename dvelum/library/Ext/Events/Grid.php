<?php
class Ext_Events_Grid extends Ext_Events_Table
{
    static protected $_editPluginOptions = array(
        'editor'=>' Ext.grid.plugin.Editing',
        'e'=>'Object',
        'eOpts'=>'Object'
    );

    public $beforeedit;
    public $canceledit;
    public $edit;
    public $validateedit;

    public function _initConfig()
    {
        parent::_initConfig();

        $this->beforeedit = static::$_editPluginOptions;
        $this->canceledit = static::$_editPluginOptions;
        $this->edit = static::$_editPluginOptions;
        $this->validateedit = static::$_editPluginOptions;
    }
}