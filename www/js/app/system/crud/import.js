
Ext.define('app.crud.Import',{
    extend:'app.import.Panel',

    initComponent:function(){

	this.callParent();
	this.uploadForm.add({
	    xtype:'combobox',
	    fieldLabel:appLang.OBJECT,
	    name:'object',
	    displayField:'title',
	    valueField:'name',
	    labelWidth:50,
	    width:350,
	    allowBlank:false,
	    forceSelection:true,
	    store: Ext.create('Ext.data.Store',{
		fields:[
		        {name:'name' , type:'string'},
		        {name:'title' , type:'string'}
		        ],
		        proxy: {
		            type: 'ajax',
		            url: this.controllerUrl + 'objects',
		            reader: {
		        	type: 'json',
					rootProperty: 'data',
		        	idProperty: 'name'
		            },
		            simpleSortMode: true
		        },
		        autoLoad: true,
		        sorters: [{
		            property : 'title',
		            direction: 'ASC'
		        }]

	    })
	});
    }
});

Ext.onReady(function(){
	appLang = Ext.apply(importLang , appLang);

	var dataPanel = Ext.create('app.crud.Import' , {
	        title:'Import Example',
		controllerUrl:app.root,
		lang:importLang
	});
	app.content.add(dataPanel);
});