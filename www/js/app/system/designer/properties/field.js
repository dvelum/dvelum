/**
 * @event objectsUpdated
 */
Ext.define('designer.properties.FieldTypeWindow',{
	extend:'Ext.Window',
	objectName:'',
	width:300,
	height:150,
	resizable:false,
	modal:true,
	setupForm:null,
	layout:'fit',
	controllerUrl:'',
	title:desLang.changeFieldType,
	
	constructor:function(){
		this.extraParams = {};
		this.callParent(arguments);
	},
	initComponent:function(){
	
		this.setupForm = Ext.create('Ext.form.Panel',{
			region: 'north',	
			bodyCls:'formBody',
			border:false,
			autoHeight:true,
			fieldDefaults:{
		        labelAlign:'left',
				anchor:'100%',
				labelWidth:150
			},
			defaults:{
				xtype:'textfield',
			    margin:'3 3 3 3 '
			},
			items:[
			      {
			    	  xtype: 'combobox',
					  typeAhead: true,
					  triggerAction: 'all',
					  selectOnTab: true,
					  fieldLabel:desLang.type,
					  labelWidth:80,
					  name:'type',
					  forceSelection:true,
					  store: [
							['Form_Field_Checkbox' , 'Checkbox'],
							['Form_Field_Combobox' , 'Combobox'],
							['Form_Field_File' , 'File'],
							['Form_Field_Hidden' , 'Hidden'],
							['Form_Field_Htmleditor' , 'Htmleditor'],
							['Form_Field_Number' , 'Number'],
							['Form_Field_Radio' , 'Radio'],
							['Form_Field_Text' , 'Text'],
							['Form_Field_Textarea' , 'Textarea'],
							['Form_Field_Time' , 'Time'],
							['Form_Field_Date' , 'Date'],
					        ['Form_Fieldset' , 'Fieldset'],
					        ['Form_Field_Display' , 'Display'],
					        ['Form_Fieldcontainer' , 'Fieldcontainer'],
					        ['Form_Checkboxgroup', 'CheckboxGroup'],
					        ['Form_Radiogroup', 'Radiogroup'],
					        ['Form_Field_Adapter','Adapter']
					  ],
					  listeners:{
			    		  select:function(field , value , options){
			    			  this.onTypeSelected(field.getValue());
			    		  },
			    		  scope:this
			    	  }
			      },{
			    	  hidden:true,
			    	  name:'adapter',
			    	  xtype: 'combobox',
					  typeAhead: true,
					  triggerAction: 'all',
					  selectOnTab: true,
					  fieldLabel:desLang.adapter,
					  labelWidth:80,
					  matchFieldWidth: false,
					  forceSelection:true,
					  valueField:'id',
					  displayField:'title',
					  qyeryMode:'local',
					  store: Ext.create('Ext.data.Store',{
						  model:'app.comboStringModel',
						  proxy: {
						        type: 'ajax',
						    	url:this.controllerUrl + 'listadapters',
						        reader: {
						            type: 'json',
									rootProperty: 'data',
						            idProperty: 'id'
						        },
							    simpleSortMode: true
						    },
					        remoteSort: false,
						    autoLoad: true,
						    sorters: [{
				                  property : 'title',
				                  direction: 'DESC'
				            }]
					  }),
					  listeners:{
			    		  select:function(field , value , options){
			    			  this.onAdapterSelected(field.getValue());
			    		  },
			    		  scope:this
			    	  }
			      },{
			    	  hidden:true,
			    	  name:'dictionary',
			    	  xtype: 'combobox',
					  typeAhead: true,
					  triggerAction: 'all',
					  selectOnTab: true,
					  fieldLabel:desLang.dictionary,
					  labelWidth:80,
					  forceSelection:true,
					  valueField:'id',
					  displayField:'title',
					  qyeryMode:'local',
					  store: Ext.create('Ext.data.Store',{
						  model:'app.comboStringModel',
						  proxy: {
						        type: 'ajax',
						    	url:this.controllerUrl + 'listdictionaries',
						        reader: {
						            type: 'json',
									rootProperty: 'data',
						            idProperty: 'id'
						        },
							    simpleSortMode: true
						    },
					        remoteSort: false,
						    autoLoad: true,
						    sorters: [{
				                  property : 'title',
				                  direction: 'DESC'
				            }]
					  }) 
			      }
			]
		});	
		this.items = [this.setupForm];
		this.buttons = [
            {
            	text:desLang.save,
		    	scope:this,
		    	handler:this.saveType
            } ,{
            	text:desLang.close,
            	scope:this,
            	handler:this.close           	
            }
		];
		this.callParent();
	},
	/**
	 * Field type selected
	 */
	onTypeSelected:function(type){
		 var form = this.setupForm.getForm();
		 if(type == 'Form_Field_Adapter'){
			 form.findField('adapter').show();
		 }else{
			 form.findField('adapter').hide();
			 form.findField('dictionary').hide();	
		 }
	},
	/**
	 * Adapter selected
	 * @param adapter
	 */
	onAdapterSelected:function(adapter){
		 var form = this.setupForm.getForm();
		 if(adapter == 'Ext_Component_Field_System_Dictionary'){
			 form.findField('dictionary').show();
		 }else{
			 form.findField('dictionary').hide();	 
		 }
	},
	/**
	 * Set field type
	 */
	saveType:function(){
	
		var type = this.setupForm.getForm().findField('type').getValue();
		
		if(type === 'Form_Field_Adapter'){
			var adapter = this.setupForm.getForm().findField('adapter').getValue();
			if(!adapter){
				Ext.Msg.alert(appLang.MESSAGE, desLang.selectAdapter);
				return;
			}
			
			if(adapter==='Ext_Component_Field_System_Dictionary')
			{
				var dictionary = this.setupForm.getForm().findField('dictionary').getValue();
				
				if(!dictionary){
					Ext.Msg.alert(appLang.MESSAGE, desLang.selectDictionary);
					return;
				}
			}
		}
		var me = this;
		var params = Ext.apply(this.extraParams , {'object':this.objectName});
		this.setupForm.getForm().submit({
			clientValidation: true,
			waitMsg:appLang.SAVING,
			method:'post',
			url:this.controllerUrl + 'changetype',
			params: params,
			success: function(form, action) {	
   		 		if(!action.result.success){
   		 			Ext.Msg.alert(appLang.MESSAGE, action.result.msg);
   		 		} else{
   		 			me.fireEvent('objectsUpdated');		 
   		 			me.close();
   		 		}
   	        },
   	        failure: app.formFailure
   	    });
	}
});
	


/**
 * Properties panel for Form_Field object
 */
Ext.define('designer.properties.Field',{
	extend:'designer.properties.Panel',

	constructor:function(){
		this.tbar = [];
		this.callParent(arguments);
	},
	initComponent:function()
	{
		
		this.tbar.push(
             {
            	 text:desLang.changeFieldType,
            	 scope:this,
            	 handler:this.selectType
             }
		);
		this.callParent();
	},
	/**
	 * Show window for type changing
	 */
	selectType:function(){
		
		 Ext.create('designer.properties.FieldTypeWindow',{
			 objectName:this.objectName,
			 controllerUrl:this.controllerUrl,
			 extraParams:this.extraParams,
			 listeners:{
				 'objectsUpdated':{
					 fn:function(){
						 this.fireEvent('objectsUpdated');
						 this.loadProperties();
					 },
					 scope:this
				 }
			 }			 
		 }).show();
	}
});