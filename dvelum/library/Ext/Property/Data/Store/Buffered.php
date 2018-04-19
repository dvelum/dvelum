<?php
class Ext_Property_Data_Store_Buffered extends Ext_Property_Data_Store
{
    public $leadingBufferZone = self::Numeric;
    public $buffered = self::Boolean;
    public $purgePageCount = self::Numeric;
    public $trackRemoved = self::Boolean;
    public $trailingBufferZone = self::Numeric;

    static public $extend = 'Ext.data.BufferedStore';
}