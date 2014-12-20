<?php
class Ext_Property_View_View extends Ext_Property_Component
{
	public $tpl = self::Object;
	public $store = self::Object;
    public $deferInitialRefresh = self::Boolean;
    public $temSelector = self::String;
    public $itemCls = self::String;
    public $itemTpl = self::Object;
    public $overItemCls = self::String;
    public $loadingText = self::String;
    /*
     * @cfg {Boolean/Object} loadMask
     */
    public $loadMask = self::Boolean;
    public $loadingCls = self::String;
    public $loadingUseMsg = self::Boolean;
    public $loadingHeight = self::Numeric;
    public $selectedItemCls = self::String;
    public $emptyText = self::String;
    public $deferEmptyText = self::Boolean;
    public $trackOver = self::Boolean;
    public $blockRefresh = self::Boolean;
    public $disableSelection = self::Boolean;
}