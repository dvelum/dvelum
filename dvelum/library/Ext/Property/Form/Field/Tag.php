<?php
class Ext_Property_Form_Field_Tag extends Ext_Property_Form_Field_Combobox
{
    public $createNewOnBlur = self::Boolean;
    public $createNewOnEnter = self::Boolean;
    public $delimiter = self::String;
    public $encodeSubmitValue = self::Boolean;
    public $filterPickList  = self::Boolean;
    public $forceSelection  = self::Boolean;
    public $grow = self::Boolean;
    public $growMax = self::Number;
    public $growMin = self::Number;
    public $labelTpl = self::Object;
    public $multiSelect  = self::Boolean;
    public $selectOnFocus = self::Boolean;
    public $stacked = self::Boolean;
    public $tipTpl = self::Object;
    public $triggerOnClick = self::Boolean;
    public $valueParam = self::String;

    static public $extend = 'Ext.form.field.Tag';
    static public $xtype = 'tagfield';
}