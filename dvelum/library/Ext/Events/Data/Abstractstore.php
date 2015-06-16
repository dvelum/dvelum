<?php
abstract class Ext_Events_Data_Abstractstore extends Ext_Events
{
	public $add = array(
			'store'=>'Ext.data.Store',
			'records'=>'Ext.data.Model[]',
			'index'=>'Number',
			'eOpts'=>'Object'
	);

    public $beforesort = array(
            'store'=>'Ext.data.Store',
            'sorters'=>'Ext.util.Sorter[]',
            'eOpts'=>'Object'
    );

    public $beginupdate = array(
            'eOpts'=>'Object'
    );
	
	public $clear = array(
			'cmp'=>'Ext.data.Store',
			'eOpts '=>'Object'
	);
	
	public $datachanged = array(
			'cmp'=>'Ext.data.Store',
			'eOpts'=>'Object'
	);

    public $endupdate = array(
            'eOpts'=>'Object'
    );

    public $refresh = array(
            'cmp'=>'Ext.data.Store',
            'eOpts'=>'Object'
    );
	
	public $remove = array(
			'store'=>'Ext.data.Store',
			'records'=>'Ext.data.Model[]',
			'index'=>'Number',
            'isMove'=>'Boolean',
			'eOpts'=>'Object'
	);

    public $sort = array(
            'store'=>'Ext.Data.Store',
            'eOpts'=>'Object'
    );

	public $update = array(
			'cmp'=>'Ext.data.Store',
			'record'=>'Ext.data.Model',
			'operation'=>'String',
            'modifiedFieldNames'=>'String[]',
            'details'=>'Object',
			'eOpts'=>'Object'
	);
}