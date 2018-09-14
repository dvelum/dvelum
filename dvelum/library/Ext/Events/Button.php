<?php
class Ext_Events_Button extends Ext_Events_Component
{
    static protected $_buttonEventOptions = array(
        'cmp'=>'Ext.button.Button',
        'e'=>'Event',
        'eOpts'=>'Object'
    );
    static protected $_menuTrgEventOptions = array(
        'cmp'=>'Ext.button.Button',
        'menu'=>'Ext.menu.Menu',
        'e'=>'Event',
        'eOpts'=>'Object'
    );
    static protected $_menuEventOptions = array(
        'cmp'=>'Ext.button.Button',
        'menu'=>'Ext.menu.Menu',
        'eOpts'=>'Object'
    );

    // Dvelum hack
    public $handler = array(
        'button' => 'Ext.button.Button',
        'e' => 'Ext.EventObject'
    );

    public $click;
    public $glyphchange = array(
        'cmp'=>'Ext.button.Button',
        'newGlyph'=>'String',
        'oldGlyph'=>'String',
        'eOpts'=>'Object'
    );
    public $iconchange = array(
        'cmp'=>'Ext.button.Button',
        'oldIcon'=>'String',
        'newIcon'=>'String',
        'eOpts'=>'Object'
    );
    public $menuhide;
    public $menushow;
    public $menutriggerout;
    public $menutriggerover;
    public $mouseout;
    public $mouseover;
    public $textchange = array(
        'cmp'=>'Ext.button.Button',
        'oldText'=>'String',
        'newtext'=>'String',
        'eOpts'=>'Object'
    );

    public $toggle = array(
        'btn'=>'Ext.button.Button',
        'pressed'=>'Boolean',
        'eOpts'=>'Object'
    );

    public $beforetoggle = array(
        'btn'=>'Ext.button.Button',
        'pressed'=>'Boolean',
        'eOpts'=>'Object'
    );

    public function _initConfig()
    {
        parent::_initConfig();

        $this->click = static::$_buttonEventOptions;
        $this->menuhide = static::$_menuEventOptions;
        $this->menushow = static::$_menuEventOptions;
        $this->menutriggerout = static::$_menuTrgEventOptions;
        $this->menutriggerover = static::$_menuTrgEventOptions;
        $this->mouseout = static::$_buttonEventOptions;
        $this->mouseover = static::$_buttonEventOptions;
    }
}