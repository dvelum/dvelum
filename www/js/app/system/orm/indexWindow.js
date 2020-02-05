/**
 * Edit window for ORM object Index
 *
 * @event dataSaved
 *
 */
Ext.define('app.crud.orm.IndexWindow', {
	extend: 'Ext.window.Window',	
	objectName:null,
	indexName:null,
	dataForm:null,
	columnsMenu:null,
	columnsList:null,
	columnsData:null,
	fieldFullText:null,
	fieldUnique:null,
	
	constructor: function(config) {
		config = Ext.apply({
			modal: true,
			layout:'fit',
			width: app.checkWidth(350),
			height:app.checkHeight(200),     
			closeAction: 'destroy',
			maximizable:true
		}, config || {});
		this.columnsData = [];	
		this.callParent(arguments);
	},
	
	/**
	 * @todo fix columns menu
	 */
	initComponent:function(){

		this.columnsMenu = Ext.create('Ext.menu.Menu',{});
		
		this.buildMenu();
		
		this.fieldFullText = Ext.create('Ext.form.field.Checkbox', {
				xtype:'checkbox',
				name:'fulltext',
	        	fieldLabel:appLang.FULLTEXT,
	        	listeners:{
					scope:this,
					change:function(field, newValue){
						if(newValue){
							this.fieldUnique.setValue(false);
						}
					}
	        	}
		});
		
		this.fieldUnique = Ext.create('Ext.form.field.Checkbox', {
        	    xtype:'checkbox',
	        	name:'unique',
	        	fieldLabel:appLang.UNIQUE,
	        	listeners:{
					scope:this,
					change:function(field, newValue){
						if(newValue){
							this.fieldFullText.setValue(false);
						}
					}
	        	}
		});
		
		this.dataForm = Ext.create('Ext.form.Panel',{
				   bodyPadding:3,
				   frame:false,
				   bodyCls:'formBody',
				   bodyBorder:false,
				   border:false,
				   fieldDefaults: {
			           labelWidth: 80,
			           labelAlign:'left',
			           anchor:'100%'
			       },
			       items:[
				          {
				        	  xtype:'textfield',
				        	  name:'name',
				        	  fieldLabel:appLang.NAME,
				        	  allowBlank:false,
				        	  vtype:'alphanum'
				          },{
				        	  xtype:'fieldcontainer',
				        	  fieldLabel:appLang.COLUMNS,
				        	  allowBlank:false,
				        	  layout: 'hbox',
				        	  items:[
				        	         {
				        	        	 xtype:'textfield',
				        	        	 readOnly:true,
				        	        	 name:'columns',
				        	        	 submitValue:false,
				        	        	 flex:1
			        	             } ,{
				    			        xtype:'button',
				    			        iconCls:'findIcon',
				    			        menu:this.columnsMenu
				    			     }
				        	  ]
				          },this.fieldUnique,
				          this.fieldFullText
				   ]
		});
		
		if(app.crud.orm.canEdit){
			this.buttons =[
			     {
			    	text:appLang.SAVE,
			    	scope:this,
			    	handler:this.saveAction
			     },
			     {
			    	text:appLang.CANCEL,
			    	scope:this,
				    handler:this.close
			     }
			];
		}
		
		this.items = [this.dataForm];

		
		
		if(this.objectName && this.indexName){
			var handle = this;
			this.on('show' , function(){				
				var params = Ext.apply({object:this.objectName,index:this.indexName});				
				this.dataForm.getForm().load({
					url:app.crud.orm.Actions.loadObjIndex,
					params:params,
					waitMsg:appLang.LOADING,
					success: function(form, action){	
		   		 		if(!action.result.success){
		   		 			Ext.Msg.alert(appLang.MESSAGE, action.result.msg);
		   		 		}else{	
		   		 			handle.columnsData = action.result.data.columns;
		   		 			handle.refreshColumns();
		   		 			handle.checkMenu();
		   		 		}
		   	        },
		   	        failure: app.formFailure
				});				
			},this);
		 }else{
			 
		 }
		
		this.callParent(arguments);
	},
	/**
	 * @param [Array] hide - fields to hide
	 * @param [Array] show - fields to show
	 * @returs void
	 */
	processFields:function(hide , show){
		
		Ext.each(hide,function(item){
			item.disable();
			item.hide();
		});
		
		Ext.each(show,function(item){
			item.enable();
			item.show();
		});	
	},
	setTableEngine:function(engine){
		switch (engine) {
			case 'Memory':
				this.processFields([this.fieldFullText], []);
				break;
				
			case 'InnoDB':
				this.processFields([this.fieldFullText], []);
				break;
	
			default:
				this.processFields([], [this.fieldFullText]);
				break;
		}
	},
	buildMenu:function(){	
		 Ext.each(this.columnsList,function(record){			    
				this.columnsMenu.add({
					text:record,
					scope:this,
					checked:false,
					checkHandler: this.menuChecked				
				});
		 },this); 
	},
	checkMenu:function(){
		this.columnsMenu.items.each(function(item){
			var checked = false;
		 	if(Ext.Array.indexOf(this.columnsData , item.text)!=-1){
		 		item.setChecked(true,true);
		 	}else{
		 		item.setChecked(false , true);
		 	}
		},this);
	},
	menuChecked:function(item , checked){
		if(checked){
			this.columnsData.push(item.text);
		}else{
			Ext.Array.remove(this.columnsData , item.text);
		}
		this.refreshColumns();
	},
	saveAction:function(){
		var handle = this;
		
		this.dataForm.getForm().submit({
			clientValidation: true,
			waitMsg: appLang.SAVING,
			method:'post',
			url:app.crud.orm.Actions.saveObjIndex,
			params:{object:this.objectName,index:this.indexName,'columns[]':this.columnsData},
			success: function(form, action) {	
   		 		if(!action.result.success){
   		 			Ext.Msg.alert(appLang.MESSAGE, action.result.msg);
   		 		} else{
   		 			handle.fireEvent('dataSaved');		 
   		 			handle.close();
   		 		}
   	        },
   	        failure: app.formFailure
   	    });
	},
	refreshColumns:function(){
		var colField = this.dataForm.getForm().findField('columns');
		var s='';
		Ext.each(this.columnsData , function(item){
			s+=item + ', ';
		}, this);
		colField.setValue(s);
	}
});