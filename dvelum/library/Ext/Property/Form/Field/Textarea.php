<?php
class Ext_Property_Form_Field_Textarea extends Ext_Property_Form_Field_Text
{
	public $enterIsSpecial = self::Boolean;
	public $preventScrollbars = self::Boolean;
	public $value = self::String;

    static public $extend = 'Ext.form.field.TextArea';
	static public $xtype = 'textarea';
}