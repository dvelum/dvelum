Ext.ns('app.crud.keys');

Ext.define('app.crud.keys.KeysModel', {
    extend: 'Ext.data.Model',
    fields: [
         {name:'id', type:'integer'},
         {name:'name', type:'string'},
         {name:'hash', type:'string'},
 	     {name:'active', type:'boolean'}
    ]
});

Ext.define('app.crud.keys.AddWindow',{
	extend:'app.editWindow',
	
	constructor: function(config) {
		config = Ext.apply({
			modal: true,
	        width: 600,
	        height: 410,      
	        resizable:false,
	        plain:true,
			title:appLang.EDIT_KEY
	    },config || {});
		this.callParent(arguments);
	},
	
	initComponent:function(){
		
		this.editForm = new Ext.form.Panel({
			frame:false,
			title:appLang.GENERAL,
		    bodyCls:'formBody',
			border:false,
			bodyBorder:false,
			bodyPadding:5,		
			fieldDefaults:{
				anchor:"100%",
				labelAlign:'right',
				labelWidth:130
			},
			items:[{
					allowBlank: false,
					fieldLabel:appLang.NAME,
					name:"name",
					xtype:"textfield",
					vtype:"alphanum",
					enableKeyEvents:true,
					listeners:{
						keyup : {
							 fn: this.checkName,
							 scope:this,
							 buffer:400
						}
					}
				},{
					fieldLabel:appLang.ENABLED,
					id:"confirmedField",
					name:"active",
					xtype:"checkbox",
					inputValue:1,
			    	uncheckedValue:0
				},{
			    	name: 'changeVal',
		        	value: 1,
		        	readOnly:true,
		        	fieldLabel:appLang.CHANGE_VALUE,
		        	checked:true,
		        	readOnly:true,
		        	xtype:'checkbox',
		        	listeners: {
		        		change : {
		        			fn:this.denyBlankPassword,
		        			scope:this,
		        			buffer:350
		        		}
		        	}
			    },{
					xtype:"textfield",
					fieldLabel:appLang.VALUE,
					inputType:"password",
					name:"hash",
					enableKeyEvents:true,
					allowBlank:false,
					enableKeyEvents:true,
					listeners:{
						keyup : {
							 fn: this.checkHash,
							 scope:this,
							 buffer:400
						}
					}
				},{
					xtype:"textfield",
					fieldLabel:appLang.VALUE_CONFIRM,
					inputType:"password",
					name:"hash2",
					submitValue:false,
					enableKeyEvents:true,
					vtype: 'valuematch',
            		initialPassField: 'hash',
            		allowBlank:false
				}
		  ]
		});
		
		this.items = [this.editForm];
		
		this.callParent();
		
		this.listeners = {
			dataLoaded:{
				fn: function(){
					var form = this.editForm.getForm();
					this.setTitle(appLang.EDIT_KEY + ': ' + form.findField('name').getValue());
					form.findField('changeVal').setReadOnly(false);
					form.findField('changeVal').setValue(0);
				},
				scope:this
			}
		};
	},
	/**
	 * Permit or prohibit be empty password field
	 * @param {Ext.form.field} field
	 * @param boolean bool
	 */
	denyBlankPassword:function(field, bool){
		var handle = this.editForm.getForm();
		
		var f1 = handle.findField('hash');
		var f2 = handle.findField('hash2');
		
		if(!bool){
			f1.disable();
			f2.disable();
		} else {
			f1.enable();
			f2.enable();
		}
	},
   /**
    * Validate unique name
    * @param {Ext.form.Field} field
    * @param {Event} event
    */
   checkName:function(field){
   		var name = field.getValue();
		var e = this.editForm.getForm().findField('id').getValue();
	   
		Ext.Ajax.request({
			url: this.controllerUrl + "checkname",
		 	method: 'post',
		 	params:{
		 		'id':e,
		 		'name':name
		 	},
			success: function(response, request) {
				response =  Ext.JSON.decode(response.responseText);
			 	if(response.success){
					field.unsetActiveError();
			 		field.clearInvalid();
			 	}else{
			 		field.markInvalid(response.msg);
			 		field.setActiveError(response.msg);
			 	}	
			},
			failure:app.ajaxFailure
		});
   },
   /**
    * Validate unique value
    * @param {Ext.form.Field} field
    * @param {Event} event
    */
   checkHash:function(field){
   		var hash = field.getValue();
		var e = this.editForm.getForm().findField('id').getValue();
	   
		Ext.Ajax.request({
			url: this.controllerUrl + "checkhash",
		 	method: 'post',
		 	params:{
		 		'id':e,
		 		'hash':hash
		 	},
			success: function(response, request) {
				response =  Ext.JSON.decode(response.responseText);
			 	if(response.success){
					field.unsetActiveError();
			 		field.clearInvalid();
			 	}else{
			 		field.markInvalid(response.msg);
			 		field.setActiveError(response.msg);
			 	}	
			},
			failure:app.ajaxFailure
		});
   }
});

Ext.define('app.crud.keys.Main',{
	extend:'Ext.grid.Panel',
	
	canEdit:false,
	canDelete:false,
	
	btnAdd:null,
	
	controllerUrl:null,
	
	searchPanel:null,
	
	initComponent: function(){
		
		this.btnAdd = Ext.create('Ext.button.Button', {
			text:appLang.ADD,
			scope:this,
			handler:function(){this.showEditWin(0);}
		});
		
	    this.searchPanel = Ext.create('SearchPanel',{			
			fieldNames:['name'],
			local:true,
			width:150,
			hideLabel:true
		});
		
		this.store = Ext.create('Ext.data.Store' , {
		    model:'app.crud.keys.KeysModel',
		 	autoLoad:true,
		 	autoSave:false,
			proxy:{
				type: 'ajax',
				url:this.controllerUrl + 'list',
			    reader: {
		            type: 'json',
					rootProperty: 'data',
		            idProperty: 'id'
		        }
			},
			sorters: [{
                  property : 'name',
                  direction: 'ASC'
            }]
		});
		
		this.columns = [];
		
		if(this.canEdit){
			this.columns.push({
				xtype:'actioncolumn',
	            width:30,
	            align:'center',
	            items: [{
	                iconCls: 'editIcon',
	                tooltip: appLang.EDIT,
	                handler: function(grid, rowIndex, colIndex) {
	                    var record = grid.getStore().getAt(rowIndex);
	                    this.showEditWin(record.get('id'));
	                },
	                scope:this
	            }]
			});
		}
		
		this.columns.push({
			text:appLang.NAME,
			dataIndex:'name',
			renderer:app.linesRenderer,
			flex:1
		},{
		    text: appLang.ACTIVE,
			dataIndex: 'active',
			width:60,
			align:'center',
			renderer:app.checkboxRenderer
		});
		
		if(this.canDelete){
			this.columns.push({
				xtype:'actioncolumn',
	            width:30,
	            align:'center',
	            items: [{
	                iconCls: 'deleteIcon',
	                tooltip: appLang.DELETE,
	                handler:this.deleteItem,
	                scope:this
	            }]
			});
		}
		
		var dockedI = [];
		if(this.canEdit){
			dockedI.push(this.btnAdd);
		}
		dockedI.push('->',this.searchPanel);
		
		this.dockedItems = [{
	        xtype: 'toolbar',
	        dock: 'top',
	        items: dockedI
	    }];
		
		this.searchPanel.store = this.store;
		
		if(this.canEdit){
			this.listeners = {
				'scope':this,
				'itemdblclick':function(view, record){
					this.showEditWin(record.get('id'));
				}
			};
		}
		
		this.callParent();
	},
	showEditWin:function(id){
		var win = Ext.create('app.crud.keys.AddWindow' , {
	   		controllerUrl:this.controllerUrl,
	   		dataItemId:id,
	   		canEdit:canEdit,
			canDelete:canDelete,
			objectName:'apikeys',
			listeners:{
				'dataSaved':{
					fn:function(){
						this.store.load();
					},
				   	scope:this
				}
		  	}
	  	}).show();
	},
	deleteItem:function(grid, rowIndex, colIndex){
		var record = grid.getStore().getAt(rowIndex);
		
		Ext.Msg.confirm(appLang.CONFIRM, appLang.MSG_CONFIRM_DELETE +' "'+record.get('name')+'"?', function(btn){
			if(btn != 'yes'){
				return;
			}
			Ext.Ajax.request({
				url:this.controllerUrl + 'delete',
				method: 'post',
				scope:this,
				params:{
					'id':record.get('id')
				},
		 		success: function(response, request) {
		 			response =  Ext.JSON.decode(response.responseText);
		 			if(response.success){
		 				this.store.removeAt(rowIndex);
		 			}else{
		 				Ext.Msg.alert(appLang.MESSAGE , response.msg);
		 			}
		 		},
		 		failure:app.ajaxFailure
			});
		},this);
	}
});

Ext.onReady(function(){
	var dataPanel = Ext.create('app.crud.keys.Main',{
		title:appLang.API_KEYS + ' :: ' + appLang.HOME,
		canEdit:canEdit,
		canDelete:canDelete,
		controllerUrl:app.root
	});
	
	app.content.add(dataPanel);	
});