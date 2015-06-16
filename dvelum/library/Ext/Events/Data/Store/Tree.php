<?php
class Ext_Events_Data_Store_Tree extends Ext_Events_Data_Store
{
    public $load = array(
        'cmp'=>'Ext.data.TreeStore',
        'records'=>'Ext.data.TreeModel[]',
        'successful'=>'Boolean',
        'operation'=>'Ext.data.Operation',
        'node'=>'Ext.data.NodeInterface',
        'eOpts'=>'Object'
    );

    public $nodeappend = array(
        'cmp'=>'Ext.data.NodeInterface',
        'node'=>'Ext.data.NodeInterface',
        'index'=>'Number',
        'eOpts'=>'Object'
    );

    public $nodebeforeappend = array(
        'cmp'=>'Ext.data.NodeInterface',
        'node'=>'Ext.data.NodeInterface',
        'eOpts'=>'Object'
    );

    public $nodebeforecollapse = array(
        'cmp'=>'Ext.data.NodeInterface',
        'eOpts'=>'Object'
    );
	
	public $nodebeforeexpand = array(
		'cmp' =>'Ext.data.NodeInterface',
		'eOpts'=>'Object'
	);
	
	public $nodebeforeinsert = array(
		'cmp' =>'Ext.data.NodeInterface',
		'node'=>'Ext.data.NodeInterface',
		'refNode'=>'Ext.data.NodeInterface',
		'eOpts'=>'Object'
	);
	
	public $nodebeforemove = array(
		'cmp' =>'Ext.data.NodeInterface',
		'oldParent'=>'Ext.data.NodeInterface',
		'newParent'=>'Ext.data.NodeInterface',
		'index'=>'Number',
		'eOpts'=>'Object'
	);
	
	public $nodebeforeremove = array(
		'cmp' =>'Ext.data.NodeInterface',
		'node'=>'Ext.data.NodeInterface',
		'isMove'=>'Boolean',
		'eOpts'=>'Object'
	);
	
	public $nodecollapse = array(
		'cmp' =>'Ext.data.NodeInterface',
		'eOpts'=>'Object'
	);
	
	public $nodeexpand = array(
		'cmp' =>'Ext.data.NodeInterface',
		'eOpts'=>'Object'
	);
	
	public $nodeinsert = array(
		'cmp' =>'Ext.data.NodeInterface',
		'node'=>'Ext.data.NodeInterface',
		'refNode'=>'Ext.data.NodeInterface',
		'eOpts'=>'Object'
	);
	
	public $nodemove = array(
		'cmp' =>'Ext.data.NodeInterface',
		'oldParent'=>'Ext.data.NodeInterface',
		'newParent'=>'Ext.data.NodeInterface',
		'index'=>'Number',
		'eOpts'=>'Object'
	);
	
	public $noderemove = array(
		'cmp' =>'Ext.data.NodeInterface',
		'node'=>'Ext.data.NodeInterface',
		'isMove'=>'Boolean',
        'context'=>'Object',
		'eOpts'=>'Object'
	);
	
	public $rootchange = array(
		'newRoot'=>'Ext.data.NodeInterface',
        'oldRoot'=>'Ext.data.NodeInterface',
		'eOpts'=>'Object'
	);
	
	public $nodesort = array(
		'cmp' =>'Ext.data.NodeInterface',
		'childNodes' =>'Ext.data.NodeInterface[]',
		'eOpts'=>'Object'
	);
}