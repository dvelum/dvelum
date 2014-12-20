<?php
class Ext_Property_Component extends Ext_Property{
/*
 * String/Object
 */
	public $autoEl = self::String;
/*
 * Boolean/String/HTMLElement/Ext.Element
 */
public $autoRender = self::Boolean;
public $anchor = self::String;
public $autoScroll = self::Boolean;
public $autoShow  = self::Boolean;
public $baseCls = self::String;
/*
 * Number/String
 */
public $border = self::String;
public $bodyPadding = self::Object;
public $bodyCls = self::String;
public $childEls = self::Object;
public $cls = self::String;
public $componentCls = self::String;

/*
 * String/Object
 */
//public $componentLayout = self::Object;

public $contentEl = self::String;
public $data  = self::Object;
public $disabled = self::Boolean;
public $disabledCls = self::String;
/*
 * Boolean/Object
 */
public $draggable = self::Boolean;
public $floating  = self::Boolean;
public $focusOnToFront = self::Boolean;
public $fieldDefaults = self::Object;
public $frame  = self::Boolean;
public $flex = self::Numeric;
public $height = self::Numeric;
public $hidden = self::Boolean;
public $hideMode = self::String;
/*
 * String/Object
 */
public $html  = self::String;
public $id = self::String;
public $items = self::Object;
public $itemId = self::String;
public $listeners = self::Object;
public $layout = self::String;
/*
 * Ext.ComponentLoader/Object
 */
public $loader = self::Object;
public $maintainFlex = self::Object;
/*
 * Number/String
 */
public $margin  = self::String;
public $maxHeight = self::Numeric;
public $maxWidth = self::Numeric;
public $minHeight = self::Numeric;
public $minWidth  = self::Numeric;
public $overCls = self::String;
/*
 * Number/String
 */
public $padding = self::Object;
public $plugins = self::Object;
public $renderData = self::Object;
public $renderSelectors = self::Object;
/*
 * String/HTMLElement/Ext.Element
 */
public $renderTo = self::String;
/*
 * Ext.XTemplate/String/String[]
 */
public $renderTpl = self::Object;
/*
 * Boolean/Object
 */
public $resizable = self::Boolean;
public $resizeHandles = self::String;
public $saveDelay = self::Numeric;
/*
 * String/Boolean
 */
public $shadow = self::Boolean;
public $stateEvents = self::Object;
public $stateId = self::String;
public $stateful = self::Boolean;
public $style = self::String;
public $styleHtmlCls = self::String;
public $styleHtmlContent = self::Boolean;
public $toFrontOnShow = self::Boolean;
public $zIndexManager = self::Object;
public $floatParent = self::Object;

/*
 * Ext.XTemplate/Ext.Template/String/String[]
 */
public $tpl = self::Object;
public $tplWriteMode = self::String;
/*
 *  String/String[]
 */
public $ui = self::String;
public $width = self::Numeric;
//public $xtype  = self::String;
}