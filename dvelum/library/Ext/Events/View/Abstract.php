<?php
class Ext_Events_View_Abstract extends Ext_Events_Component
{
    public $beforerefresh = [
        'cmp'=>'Ext.view.View',
        'eOpts'=>'Object'
    ];

    public $itemadd =[
        'records' =>'Ext.data.Model[]',
        'index' =>'Number',
        'node' =>'HTMLElement[]',
        'eOpts'=>'Object'
    ];

    public $itemremove =[
        'records' =>'Ext.data.Model[]',
        'index' =>'Number',
        'item' => 'HTMLElement[]',
        'view' =>'Ext.view.View',
        'eOpts'=>'Object'
    ];

    public $itemupdate =[
        'record' =>'Ext.data.Model',
        'index' =>'Number',
        'node' =>'HTMLElement',
        'eOpts'=>'Object'
    ];

    public $refresh = [
        'cmp'=>'Ext.view.View',
        'eOpts'=>'Object'
    ];

    public $viewready = [
        'cmp'=>'Ext.view.View',
        'eOpts'=>'Object'
    ];
}