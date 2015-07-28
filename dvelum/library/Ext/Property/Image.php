<?php
class Ext_Property_Image extends Ext_Property_Component
{
	public $alt = self::String;
	public $imgCls = self::String;
	public $src = self::String;
	public $title = self::String;

	static public $extend = 'Ext.Img';
	static public $xtype = 'image';
}