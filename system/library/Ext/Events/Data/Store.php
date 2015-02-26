<?php
class Ext_Events_Data_Store extends Ext_Events_Data_Abstractstore
{
	public $beforeprefetch = array(
			'store'=>'Ext.data.Store',
			'operation'=>'Ext.data.Operation',
			'eOpts'=>'Object'
	);
	
	public $groupchange = array(
			'store'=>'Ext.data.Store',
			'groupers'=>'Ext.util.Grouper[]',
			'eOpts '=>'Object'
	);
	
	public $prefetch = array(
			'store'=>'Ext.data.Store',
			'records'=>'Ext.data.Model[]',
			'successful' => 'Boolean',
			'operation'=>'Ext.data.Operation',
			'eOpts'=>'Object'
	);
}