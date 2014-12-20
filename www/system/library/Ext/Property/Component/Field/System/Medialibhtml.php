<?php
class Ext_Property_Component_Field_System_Medialibhtml extends Ext_Property_Panel
{
	public $editorName = self::String;
	public $title = self::String;
	public $width = self::Number;
	public $height = self::Number;
	
	static public $extend = 'app.medialib.HtmlPanel';
	static public $xtype = 'medialibhtmlpanel';
}