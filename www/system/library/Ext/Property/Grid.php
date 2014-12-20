<?php
class Ext_Property_Grid extends Ext_Property_Table
{
    public $autoHeight = self::Boolean;
	public $columnLines = self::Boolean;
	public $bbar = self::Object;
	public $plugins = self::Object;
	public $features = self::Object;
	public $invalidateScrollerOnRefresh = self::Boolean;

    static public $extend = 'Ext.grid.Panel';
    static public $xtype = 'grid';
}