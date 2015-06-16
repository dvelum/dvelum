<?php
class Ext_Events_Data_Store extends Ext_Events_Data_Proxystore
{
	public $beforeprefetch = array(
			'cmp'=>'Ext.data.Store',
			'operation'=>'Ext.data.operation.Operation',
			'eOpts'=>'Object'
	);
	
	public $groupchange = array(
			'store'=>'Ext.data.Store',
			'grouper'=>'Ext.util.Grouper',
			'eOpts '=>'Object'
	);

    public $filterchange = array(
            'store'=>'Ext.data.Store',
            'filters'=>'Ext.Util.Filter[]',
            'eOpts'=>'Object'
    );
	
	public $prefetch = array(
			'cmp'=>'Ext.data.Store',
			'records'=>'Ext.data.Model[]',
			'successful' => 'Boolean',
			'operation'=>'Ext.data.operation.Operation',
			'eOpts'=>'Object'
	);
}