/**
 * Properties panel for Grid object
 */
Ext.define('designer.properties.FilterComponent',{
	extend:'designer.properties.Panel',
	layout:'accordion',
	fieldProperties: null,
	initComponent:function()
	{
        this.eventsControllerUrl = app.createUrl([designer.controllerUrl ,'filterEvents','']);

		this.mainConfigTitle = desLang.properties;

		this.fieldsStore = Ext.create('Ext.data.Store',{
			  model:'app.comboStringModel',
			  proxy: {
			        type: 'ajax',
			    	url:this.controllerUrl + 'storefields',
			        reader: {
			            type: 'json',
						rootProperty: 'data',
			            idProperty: 'id'
			        },
			        extraParams:{
			        	object:this.objectName
			        },
				    simpleSortMode: true
			    },
		        remoteSort: false,
			    autoLoad: true,
			    sorters: [{
	                  property : 'id',
	                  direction: 'ASC'
	            }]
		});	
				

		this.sourceConfig = Ext.apply({	
			'storeField':{
				editor:Ext.create('Ext.form.ComboBox', {
					 selectOnFocus:true,
					 editable:true,
			   	     triggerAction: 'all',
			   	     anchor:'100%',
			   	     queryMode: 'remote',
			   	     store:this.fieldsStore,
			   	     valueField: 'id',
			   	     displayField: 'id',
			   	     allowBlank:true,
			   	     forceSelection:false
				})
			},
			'type':{
				editor:Ext.create('Ext.form.field.ComboBox',{
					typeAhead: true,
				    triggerAction: 'all',
				    selectOnTab: true,
				    labelWidth:80,
				    forceSelection:true,
				    queryMode:'local',
				    store: [
				        ['Component' , desLang.component],
				        ['Field', desLang.field]
				    ]
				})
			}
		});

		
		var me = this;		
		
		this.callParent();	
		
		
		/**
		 * Standard field properties editor
		 */
		this.fieldProperties = Ext.create('designer.properties.Field',{
			title:desLang.advancedOptions,
			controllerUrl:app.createUrl([designer.controllerUrl ,'filter','']),
			objectName:this.objectName,
			application:me,
            showEvents:false
		});
		
		this.fieldProperties.on('dataSaved',function(){
			this.fireEvent('dataSaved');
		},this);
		
		this.fieldProperties.on('objectsUpdated',function(){
			this.fireEvent('objectsUpdated');
		},this);
		
		this.add(this.fieldProperties);
	},
    destroy:function(){
        this.fieldsStore.destroy();
        this.fieldProperties.destroy();
        this.callParent(arguments);
    }
});    
