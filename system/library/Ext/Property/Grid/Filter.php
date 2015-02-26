<?php
abstract class Ext_Property_Grid_Filter extends Ext_Property
{
  public $listeners = self::Object;
  public $active  = self::Boolean;
  public $dataIndex = self::String;
  public $updateBuffer = self::Numeric;
  public $type = self::String;
  
  static public $xtype = '';
}