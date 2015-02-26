<?php
class Ext_Property_Form_Field_Htmleditor extends Ext_Property_Component
{
	public $enableFormat = self::Boolean;
	public $enableFontSize = self::Boolean;
	public $enableColors = self::Boolean;
	public $enableAlignments = self::Boolean;
	public $enableLists = self::Boolean;
	public $enableSourceEdit = self::Boolean;
	public $enableLinks = self::Boolean;
	public $enableFont = self::Boolean;
	public $createLinkText = self::String;
	public $defaultLinkValue = self::String;
	public $fontFamilies = self::Object;
	public $fieldLabel = self::String;
	public $defaultFont = self::String;
	public $defaultValue = self::String;
	public $name = self::String;
	
	static public $extend = 'Ext.form.field.HtmlEditor';
	static public $xtype = 'htmleditor';
}