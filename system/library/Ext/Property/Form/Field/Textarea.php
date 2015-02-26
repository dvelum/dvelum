<?php
class Ext_Property_Form_Field_Textarea extends Ext_Property_Form_Field_Text
{
    public $growMin = self :: Number;
    public $growMax = self :: Number;
    public $growAppend = self :: String;
    public $cols = self :: Number;
    public $rows  = self :: Number;
    public $enterIsSpecial = self :: Boolean;
    public $preventScrollbars = self :: Boolean;
    
    static public $extend = 'Ext.form.field.TextArea';
	static public $xtype = 'textarea';
}