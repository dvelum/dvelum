<?php
class Ext_Property_Component_Field_System_Objectlink extends Ext_Property_Form_Fieldcontainer
{
    public $objectName = self::String;
    public $controllerUrl = self::String;
    public $hideId  = self::Boolean;
    public $allowBlank  = self::Boolean;
    public $extraParams = self::Object;
    public $readOnly = self::Boolean;
    public $name = self::String;
    public $valueType = self::String;

    static public $extend = 'app.objectLink.Field';
    static public $xtype = 'objectlinkfield';
}