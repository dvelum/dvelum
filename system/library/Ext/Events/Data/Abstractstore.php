<?php
abstract class Ext_Events_Data_Abstractstore extends Ext_Events
{
	public $add = array(
			'store'=>'Ext.data.Store',
			'records'=>'Ext.data.Model[]',
			'index'=>'Number',
			'eOpts'=>'Object'
	);
	
	public $beforeload = array(
			'store'=>'Ext.data.Store',
			'operation'=>'Ext.data.Operation',
			'eOpts '=>'Object'
	);
	
	public $beforesync = array(
			'options'=>'Object',
			'eOpts'=>'Object'
	);
	
	public $clear = array(
			'store'=>'Ext.data.Store',
			'eOpts '=>'Object'
	);
	
	public $datachanged = array(
			'this'=>'Ext.data.Store',
			'eOpts'=>'Object'
	);
	
	public $remove = array(
			'store'=>'Ext.data.Store',
			'record'=>'Ext.data.Model',
			'index'=>'Number',
			'eOpts'=>'Object'
	);
	
	public $update = array(
			'store'=>'Ext.data.Store',
			'record'=>'Ext.data.Model',
			'operation'=>'String',
			'eOpts'=>'Object'
	);
	
	public $write = array(
			'store'=>'Ext.data.Store',
			'operation'=>'Ext.data.Operation',
			'eOpts'=>'Object'
	);
	
	public $load = array(
			'store'=>'Ext.data.Store',
			'records'=>'Ext.util.Grouper[]',
			'successful'=>'Boolean',
			'operation'=>'Ext.data.Operation',
			'eOpts'=>'Object'
	);
}