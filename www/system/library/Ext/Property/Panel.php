<?php
class Ext_Property_Panel extends Ext_Property_Container{

	public $isPanel = self::Boolean;
	public $columns = self::Number;
	public $columnWidth = self::Number;	
	public $defaultDockWeights = self::Object;
	public $defaults = self::Object;

	public $collapsedCls = self::String;
    public $animCollapse = self::Boolean;
    public $minButtonWidth = self::Numeric;
    public $manageHeight = self::Boolean;
    public $collapsed = self::Boolean;
    public $collapseFirst = self::Boolean;
    public $hideCollapseTool = self::Boolean;
    public $titleCollapse = self::Boolean;
    public $collapseMode = self::String;
    public $placeholder = self::Object;
    public $floatable = self::Boolean;
    public $overlapHeader = self::Boolean;
    public $collapsible = self::Boolean;
    public $collapseDirection = self::String;
    public $closable = self::Boolean;
    public $closeAction = self::String;
    public $preventHeader = self::Boolean;
    public $headerPosition = self::String;
    public $frame = self::Boolean;
    public $frameHeader = self::Boolean;
    public $tools = self::Object;
    public $title = self::String;
    public $iconCls = self::String;
    public $icon = self::String;
    public $titleAlign  = self::String;
    public $dock = self::String;
    
    public $tooltip = self::String;
    public $tooltipType = self::String;
    
    public $region = self::String;
    public $split = self::Boolean;
    
    
    
    // abstract panel
    public $baseCls = self::String;
    public $bodyBorder  = self::Boolean;
    public $bodyCls = self::String;
    public $bodyPadding = self::Number;
    public $bodyStyle= self::String;
    public $border = self::Number;
    public $componentLayout  = self::String;
	public $dockedItems = self::Object;
	public $shrinkWrapDock = self::Number;
    
    
    static public $extend = 'Ext.Panel';
    static public $xtype = 'panel';
}