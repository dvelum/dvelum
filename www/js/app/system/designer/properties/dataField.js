/**
 * Properties panel for Data Field object
 */
Ext.define('designer.properties.dataField',{
	extend:'designer.properties.Panel',
	
	fieldtypes: [['boolean'],['integer'],['float'],['string'],['date']],
	directions: [['ASC'],['DESC']],
	
	initComponent:function()
	{
		this.sourceConfig = Ext.apply({	
			'type':{
				editor:Ext.create('Ext.form.ComboBox', {
					 selectOnFocus:true,
		  	    	 typeAhead:true,
					 editable:true,
			   	     triggerAction: 'all',
			   	     anchor:'100%',
		  	   	     queryMode: 'local',
		  	   	     forceSelection:true,
			   	     store:Ext.create('Ext.data.ArrayStore',{
						        fields: ['id'],
						        data: this.fieldtypes
							 }),
			   	     valueField: 'id',
			   	     displayField: 'id',
			   	     allowBlank:true
				})
			},
			'sortDir':{
				editor:Ext.create('Ext.form.ComboBox', {
					 selectOnFocus:true,
		 	    	 typeAhead:true,
					 editable:true,
			   	     triggerAction: 'all',
			   	     anchor:'100%',
		 	   	     queryMode: 'local',
		 	   	     forceSelection:true,
			   	     store:Ext.create('Ext.data.ArrayStore',{
						        fields: ['id'],
						        data: this.directions
					 }),
			   	     valueField: 'id',
			   	     displayField: 'id',
			   	     allowBlank:true
				})
			}
		});
		this.callParent();	
	}
});