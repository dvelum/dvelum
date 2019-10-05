<?php
class Ext_Property_Component_Field_System_Objectslist extends Ext_Property_Panel
{
    public $name = self::String;
    public $objectName = self::String;
    public $controllerUrl = self::String;

    public $extraParams = self::Object;
    public $readOnly = self::Boolean;

    public $sortColumn = self::Boolean;
    public $deleteColumn = self::Boolean;
    public $statusColumn = self::Boolean;

    public $addButtonText = self::String;
    public $addAllButtonText = self::String;
    public $dataColumnTitle = self::String;
    public $dataColumnIndex = self::String;
    public $fieldName = self::String;

    public $showAddAllButton = self::Boolean;

    static public $extend = 'app.objectLink.Panel';
    static public $xtype = 'objectlinkpanel';
}