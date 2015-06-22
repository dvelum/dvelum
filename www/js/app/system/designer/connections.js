Ext.ns('designer.connections');

Ext.define('designer.connections.Model', {
	extend: 'Ext.data.Model',
	fields: [
		{name:'id' , type:'integer'},
		{name:'name' , type:'string'},
		{name:'host' , type:'string'},
		{name:'base' , type:'string'},
		{name:'user', type:'string'}
	]
});

Ext.define('designer.connections.Window',{
	extend:'Ext.Window',

	dataGrid:null,
	dataStore:null,

	controllerUrl:'',

	constructor: function(config) {
		config = Ext.apply({
			title: desLang.dbConnections,
			width: 700,
			height:300,
			layout:'fit',
			closeAction: 'destroy',
			maximizable:true
		}, config || {});
		this.callParent(arguments);
	},

	initComponent:function(){

		var me = this;

		this.dataStore = Ext.create('Ext.data.Store', {
			model: 'designer.connections.Model',
			proxy: {
				type: 'ajax',
				url:this.controllerUrl +  '/db/list',
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
				property : 'host',
				direction: 'DESC'
			}]
		});

		this.dataGrid = Ext.create('Ext.grid.Panel',{
			store: this.dataStore,
			viewConfig:{
				stripeRows:false
			},
			frame: false,
			loadMask:true,
			columnLines: true,
			scrollable:true,
			tbar:[
				{
					text:appLang.ADD,
					handler:function(){
						this.showEditWindow(null);
					},
					scope:this
				}
			],
			columns: [
				{
					xtype:'actioncolumn',
					iconCls:'editIcon',
					align:'center',
					tooltip:appLang.EDIT_ITEM,
					width:30,
					scope:me,
					handler:function(grid,row,col){
						me.showEditWindow(grid.getStore().getAt(row).get('id'));
					}
				},
				{
					sortable: true,
					text:desLang.name,
					dataIndex:'name',
					align:'left',
					width:150,
					flex:1
				},{
					sortable: true,
					text:desLang.dbHost,
					dataIndex:'host',
					align:'center',
					width:150
				},{
					sortable: true,
					text:desLang.dbBase,
					dataIndex: 'base',
					align:'center',
					width:150
				},{
					sortable: true,
					text:desLang.dbUser,
					dataIndex: 'user',
					align:'center',
					width:150
				},{
					xtype:'actioncolumn',
					width:20,
					items:[{
						tooltip:desLang.remove,
						iconCls:'deleteIcon',
						handler:this.removeItem,
						scope:this
					}]
				}
			],
			listeners : {
				'itemdblclick':{
					fn:function(view , record , number , event , options){
						this.showEditWindow(record.get('id'));
					},
					scope:this
				}
			}
		});
		this.items = [this.dataGrid];
		this.callParent(arguments);
	},
	/**
	 * Remove database connection
	 * @param grid
	 * @param rowIndex
	 * @param colIndex
	 */
	removeItem:function(grid, rowIndex, colIndex){
		var rec = grid.getStore().getAt(rowIndex);
		Ext.Ajax.request({
			url:this.controllerUrl +  '/db/remove',
			method: 'post',
			scope:this,
			params:{'id':rec.get('id')},
			success: function(response, request) {
				response =  Ext.JSON.decode(response.responseText);
				if(response.success){
					grid.getStore().remove(rec);
				} else {
					Ext.Msg.alert(appLang.MESSAGE , response.msg);
				}
			},
			failure:function() {
				Ext.Msg.alert(appLang.MESSAGE , appLang.MSG_LOST_CONNECTION);
			}
		});
	},
	/**
	 * Show connection editor window
	 * @param recordId
	 */
	showEditWindow:function(recordId){
		var win = Ext.create('designer.connections.EditWindow',{
			recordId:recordId,
			controllerUrl:this.controllerUrl
		});

		win.on('dataSaved',function(){
			this.dataStore.load();
		},this);

		win.show();
	}
});
/**
 * @event dataSaved
 */
Ext.define('designer.connections.EditWindow',{
	extend:'Ext.Window',

	dataForm:null,
	recordId:null,

	controllerUrl:'',

	constructor: function(config) {
		config = Ext.apply({
			title: desLang.dbConnections,
			width: 400,
			height:300,
			layout:'fit',
			modal:true,
			closeAction: 'destroy',
			maximizable:true
		}, config || {});
		this.callParent(arguments);
	},
	initComponent:function(){
		this.dataForm = Ext.create('Ext.form.Panel',{
			bodyCls:'formBody',
			bodyPadding:10,
			fieldDefaults:{
				anchor:'100%',
				labelWidth:140
			},
			items:[

				{
					xtype:'hidden',
					allowBlank:false,
					value:-1,
					name:'id'
				},{
					xtype:'textfield',
					fieldLabel:desLang.name,
					allowBlank:false,
					name:'name'
				},{
					xtype:'textfield',
					fieldLabel:desLang.dbHost,
					allowBlank:false,
					name:'host'
				},{
					xtype:'textfield',
					fieldLabel:desLang.dbUser,
					allowBlank:false,
					name:'user'
				},{
					xtype:'textfield',
					fieldLabel:desLang.dbBase,
					allowBlank:false,
					name:'base'
				},{
					name: 'setpass',
					value: 1,
					readOnly:true,
					fieldLabel:desLang.changePassword,
					submitValue:false,
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
					fieldLabel:desLang.password,
					inputType:"password",
					name:"pass",
					xtype:"textfield",
					enableKeyEvents:true,
					allowBlank:false
				},{
					fieldLabel:desLang.passwordConfirm,
					inputType:"password",
					name:"pass2",
					submitValue:false,
					xtype:"textfield",
					enableKeyEvents:true,
					vtype: 'password',
					initialPassField: 'pass',
					allowBlank:false
				}
			]
		});

		this.buttons = [
			{
				text:desLang.cancel,
				scope:this,
				handler:this.close
			},{
				text:desLang.test,
				scope:this,
				handler:this.testAction
			},{
				text:desLang.save,
				scope:this,
				handler:this.saveAction
			}
		];

		this.items = [this.dataForm];

		this.callParent(arguments);

		if(this.recordId!==null){
			this.dataForm.load({
				url:this.controllerUrl +  '/db/load',
				params:{id:this.recordId},
				scope:this,
				success: function(form, action){
					form.findField('setpass').enable();
					form.findField('setpass').setValue(false);
					form.findField('setpass').setReadOnly(false);
				}
			});
		}
	},
	/**
	 * Permit or rapretit be empty password field
	 * @param {Ext.form.field} field
	 * @param boolean bool
	 */
	denyBlankPassword:function(field, bool){
		var handle = this.dataForm.getForm();

		if(!bool){
			handle.findField('pass').disable();
			handle.findField('pass2').disable();
		} else {
			handle.findField('pass').enable();
			handle.findField('pass2').enable();
		}

		handle.findField('pass').allowBlank = !bool;
		handle.findField('pass2').allowBlank = !bool;
	},
	/**
	 * Save db Connection config
	 */
	saveAction:function(){
		this.dataForm.getForm().submit({
			clientValidation: true,
			waitMsg: appLang.SAVING,
			method:'post',
			scope:this,
			url:this.controllerUrl +  '/db/save',
			success: function(form, action) {
				if(!action.result.success){
					Ext.Msg.alert(appLang.MESSAGE, action.result.msg);
				} else{
					this.fireEvent('dataSaved');
					this.close();
				}
			},
			failure: app.formFailure
		});
	},
	/**
	 * Test database connection
	 */
	testAction:function(){
		this.dataForm.getForm().submit({
			clientValidation: true,
			waitMsg: desLang.checking,
			method:'post',
			scope:this,
			url:this.controllerUrl +  '/db/test',
			success: function(form, action) {
				if(action.result.success){
					Ext.Msg.alert(appLang.MESSAGE, desLang.connectionSuccess);
				} else{
					Ext.Msg.alert(appLang.MESSAGE, action.result.msg);
				}
			},
			failure: app.formFailure
		});
	}
});
