<?php
class Ext_Property_Panel extends Ext_Property_Container{

	public $animCollapse = self::Boolean;
	public $bbar = self::Object;
	public $bodyBorder = self::Object;
	public $bodyCls = self::String;
	public $bodyPadding = self::Number;
	public $bodyStyle = self::String;
	public $buttonAlign = self::String;
	public $buttons = self::Object;
	public $closable = self::Boolean;
	public $closeAction = self::String;
	public $collapseDirection = self::String;
	public $collapseFirst = self::Boolean;
	public $collapseMode = self::String;
	public $collapsed = self::Boolean;
	public $collapsedCls = self::String;
	public $collapsible = self::Boolean;
	public $constrainHeader = self::Boolean;
	public $dockedItems = self::Object;
	public $fbar = self::Object;
	public $floatable = self::Boolean;
	public $frameHeader = self::Boolean;
	public $glyph = self::String;
	public $header = self::Boolean;
	public $headerOverCls = self::String;
	public $headerPosition = self::String;
	public $hideCollapseTool = self::Boolean;
	public $icon = self::String;
	public $iconAlign = self::String;
	public $iconCls = self::String;
	public $lbar = self::Object;
	public $manageHeight = self::Boolean;
	public $minButtonWidth = self::Number;
	public $overlapHeader = self::Boolean;
	public $placeholder = self::Object;
	public $placeholderCollapseHideMode = self::Number;
	public $rbar = self::Object;
	public $shrinkWrapDock = self::Number;
	public $simpleDrag = self::Boolean;
	public $tbar = self::Object;
	public $title = self::String;
	public $titleAlign = self::String;
	public $titleCollapse = self::Boolean;
	public $titleRotation = self::String;
	public $tools = self::Object;

    // border layout
    public $split = self::Boolean;
    
    static public $extend = 'Ext.panel.Panel';
    static public $xtype = 'panel';
}