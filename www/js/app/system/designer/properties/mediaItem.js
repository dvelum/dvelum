/**
 * Properties panel for Window object
 */
Ext.define('designer.properties.MediaItem',{
	extend:'designer.properties.Field',
	
	initComponent:function()
	{
		this.sourceConfig = Ext.apply({	
			'resourceType':{
				editor:Ext.create('Ext.form.ComboBox', {
					 selectOnFocus:true,
					 editable:true,
			   	     triggerAction: 'all',
			   	     anchor:'100%',
			   	     queryMode: 'local',
			   	     store:app.medialib.typesStore,
			   	     valueField: 'id',
			   	     displayField: 'title',
			   	     allowBlank:true
				})
			}
		});
		this.callParent();	
	}
});