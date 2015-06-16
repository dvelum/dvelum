<?php
class Ext_Property_Grid_Filtersfeature extends Ext_Property_Grid_Feature
{
  public $autoReload = self::Boolean;
  public $encode = self::Boolean;
  public $filterCls = self::String;
  public $filters = self::Object;
  public $local = self::Boolean;
  public $menuFilterText  = self::String;
  public $paramPrefix  = self::String;
  public $showMenu = self::Boolean;
  public $stateId = self::String;
  public $updateBuffer = self::Numeric;
  
  static public $ftype = 'filters';
}