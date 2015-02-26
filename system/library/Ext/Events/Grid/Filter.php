<?php
class Ext_Events_Grid_Filter extends Ext_Events
{
  public $activate = array(
    'ct'=> 'Ext.ux.grid.filter.Filter',
    'eOpts'=>'Object'
  );  
  public $deactivate = array(
    'ct'=> 'Ext.ux.grid.filter.Filter',
    'eOpts'=>'Object'
  ); 
  public $serialize = array(
    'data'=>'Array/Object',
    'filter'=>'Ext.ux.grid.filter.Filter',
    'eOpts'=>'Object'
  );  
  public $update = array(
    'ct'=> 'Ext.ux.grid.filter.Filter',
    'eOpts'=>'Object'
  ); 
}