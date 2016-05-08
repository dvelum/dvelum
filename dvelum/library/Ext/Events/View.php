<?php
class Ext_Events_View extends Ext_Events_View_Abstract
{
    static protected $_componentEventOptions = array(
        'cmp'=>'Ext.view.View',
        'e'=> 'Ext.event.Event',
        'eOpts'=>'Object'
    );

    static protected $_componentEventItemOptions = array(
        'cmp'=>'Ext.view.View',
        'record' => 'Ext.data.Model',
        'item' => 'HTMLElement',
        'index' => 'Number',
        'e'=> 'Ext.event.Event',
        'eOpts'=>'Object'
    );

    public $beforedeselect = [
        'cmp'=>'Ext.view.View',
        'record' => 'Ext.data.Model',
        'index' => 'number',
        'eOpts'=>'Object'
    ];

    public $deselect = [
        'cmp'=>'Ext.view.View',
        'record' => 'Ext.data.Model',
        'eOpts'=>'Object'
    ];

    public $focuschange = [
        'cmp'=>'Ext.view.View',
        'oldFocused' => 'Ext.data.Model',
        'newFocused' => 'Ext.data.Model',
        'eOpts'=>'Object'
    ];

    public $highlightitem = [
        'view'=>'Ext.view.View',
        'node' => 'Ext.dom.Element',
        'eOpts'=>'Object'
    ];

    public $select = [
        'cmp'=>'Ext.view.View',
        'record' => 'Ext.data.Model',
        'index' => 'number',
        'eOpts'=>'Object'
    ];

    public $selectionchange = [
        'cmp'=>'Ext.view.View',
        'selected' => 'Ext.data.Model[]',
        'eOpts'=>'Object'
    ];

    public $unhighlightitem = [
        'view'=>'Ext.view.View',
        'node' => 'Ext.dom.Element',
        'eOpts'=>'Object'
    ];

    public $beforeselect = [
        'cmp'=>'Ext.view.View',
        'record' => 'Ext.data.Model',
        'index' => 'number',
        'eOpts'=>'Object'
    ];

    public function _initConfig()
    {
        parent::_initConfig();

        $this->beforecontainerclick = static::$_componentEventOptions;
        $this->beforecontainerclick = static::$_componentEventOptions;
        $this->beforecontainercontextmenu = static::$_componentEventOptions;
        $this->beforecontainerdblclick = static::$_componentEventOptions;
        $this->beforecontainerkeydown = static::$_componentEventOptions;
        $this->beforecontainerkeypress = static::$_componentEventOptions;
        $this->beforecontainerkeyup = static::$_componentEventOptions;
        $this->beforecontainermousedown = static::$_componentEventOptions;
        $this->beforecontainermouseout = static::$_componentEventOptions;
        $this->beforecontainermouseover = static::$_componentEventOptions;
        $this->beforecontainermouseup = static::$_componentEventOptions;

        $this->containerclick  = static::$_componentEventOptions;
        $this->containercontextmenu  = static::$_componentEventOptions;
        $this->containerdblclick  = static::$_componentEventOptions;
        $this->containerkeydown  = static::$_componentEventOptions;
        $this->containerkeypress  = static::$_componentEventOptions;
        $this->containerkeyup  = static::$_componentEventOptions;
        $this->containermousedown  = static::$_componentEventOptions;
        $this->containermouseout  = static::$_componentEventOptions;
        $this->containermouseover  = static::$_componentEventOptions;
        $this->containermouseup = static::$_componentEventOptions;

        $this->beforeitemclick  = static::$_componentEventItemOptions;
        $this->beforeitemcontextmenu  = static::$_componentEventItemOptions;
        $this->beforeitemdblclick  = static::$_componentEventItemOptions;
        $this->beforeitemkeydown  = static::$_componentEventItemOptions;
        $this->beforeitemkeypress  = static::$_componentEventItemOptions;
        $this->beforeitemkeyup  = static::$_componentEventItemOptions;
        $this->beforeitemmousedown  = static::$_componentEventItemOptions;
        $this->beforeitemmouseenter  = static::$_componentEventItemOptions;
        $this->beforeitemmouseleave  = static::$_componentEventItemOptions;
        $this->beforeitemmouseup  = static::$_componentEventItemOptions;

        $this->itemclick   = static::$_componentEventItemOptions;
        $this->itemcontextmenu   = static::$_componentEventItemOptions;
        $this->itemdblclick   = static::$_componentEventItemOptions;
        $this->itemkeydown   = static::$_componentEventItemOptions;
        $this->itemkeypress   = static::$_componentEventItemOptions;
        $this->itemkeyup   = static::$_componentEventItemOptions;
        $this->itemmousedown  = static::$_componentEventItemOptions;
        $this->itemmouseenter   = static::$_componentEventItemOptions;
        $this->itemmouseleave   = static::$_componentEventItemOptions;
        $this->itemmouseup   = static::$_componentEventItemOptions;
    }
}