Ext.ns('app.report.filter');

/**
 *
 *  @event dataChanged
 *
 */
Ext.define('app.report.filter.Main',{
	extend:'Ext.Panel',
	layout:'border',
	border:false,
	defaults:{
		border:false
	},
	controllerUrl:'',
	canEdit:false,
	canDelete:false,
	minWidth:100,

	initComponent:function(){
		var me = this;
		this.conditions = Ext.create('app.report.filter.Conditions',{
			title:appLang.CONDITIONS,
			region:'north',
			split:true,
			controllerUrl:this.controllerUrl,
			canEdit:this.canEdit,
			height:200,
			canDelete:this.canDelete
		});


		this.filters = Ext.create('app.report.filter.Filters',{
			title:appLang.FILTERS,
			region:'center',
			controllerUrl:this.controllerUrl,
			canEdit:this.canEdit,
			canDelete:this.canDelete
		});

		this.conditions.on('dataChanged',function(){
			this.fireEvent('dataChanged');
		},this);

		this.filters.on('dataChanged',function(){
			this.fireEvent('dataChanged');
		},this);

		this.items = [this.conditions /*, this.filters*/];
		this.callParent(arguments);

	}
});


Ext.define('app.report.filter.ConditionsModel', {
	extend: 'Ext.data.Model',
	fields: [
		{name:'id' , type:'integer'},
		{name:'object' , type:'string'},
		{name:'field' , type:'string'},
		{name:'value' , type:'string'},
		{name:'value2' , type:'string'},
		{name:'operator' , type:'string'}
	]
});

Ext.define('app.report.filter.FiltersModel', {
	extend: 'Ext.data.Model',
	fields: [
		{name:'id' , type:'integer'},
		{name:'object' , type:'string'},
		{name:'field' , type:'string'},
		{name:'group', type:'integer'},
		{name:'type', type:'string'}
	]
});

/**
 *
 * @event dataChanged
 *
 */
Ext.define('app.report.filter.Conditions',{
	extend:'Ext.panel.Panel',
	controllerUrl:'',
	frame: false,
	scrollable:true,
	minHeignt:100,
	height:300,
	canEdit:false,
	canDelete:false,

	layout:'fit',

	initComponent:function(){

		this.dataStore = Ext.create('Ext.data.Store', {
			model: 'app.report.filter.ConditionsModel',
			proxy: {
				type: 'ajax',
				url:this.controllerUrl +  'conditionslist',
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
				property : 'id',
				direction: 'DESC'
			}]
		});

		this.tbar = [{
			text:appLang.ADD_ITEM,
			scope:this,
			handler:function(grid , row , col){
				this.showEditWindow(null);
			}
		}];

		this.dataGrid = Ext.create('Ext.grid.Panel',{
			store:this.dataStore,
			columns:[
				{
					xtype:'actioncolumn',
					width:40,
					align:'center',
					items:[{
						iconCls:'editIcon',
						tooltip:appLang.EDIT_ITEM,
						scope:this,
						width:40,
						handler:function(grid , row , col){
							this.showEditWindow(grid.getStore().getAt(row).get('id'));
						}
					}]
				},{
					text:appLang.OBJECT,
					dataIndex:'object',
					width:80
				},{
					text:appLang.FIELD,
					dataIndex:'field',
					width:90
				},{
					text:appLang.OPERATOR,
					dataIndex:'operator',
					width:100,
					align:'center'
				},{
					text:appLang.VALUE,
					dataIndex:'value',
					width:100
				},{
					text:appLang.VALUE + '2',
					dataIndex:'value2',
					width:100
				},{
					xtype:'actioncolumn',
					width:35,
					items:[{
						iconCls:'deleteIcon',
						scope:this,
						width:35,
						tooltip:appLang.DELETE_ITEM,
						handler:this.removeRecord
					}]
				}
			],
			viewConfig:{
				stripeRows:false
			},
			frame: false,
			loadMask:true,
			columnLines: true,
			listeners:{
				'itemdblclick':{
					fn:function(view , record , number , event , options){
						this.showEditWindow(record.get('id'));
					},
					scope:this
				}
			}
		});

		this.items = [this.dataGrid];

		this.callParent();


		this.on('resize',function(){
			//this.dataGrid.doComponentLayout();
		},this);
	},
	/**
	 * Remove condition Action
	 * @param grid
	 * @param rowIndex
	 * @param colIndex
	 */
	removeRecord:function(grid, rowIndex, colIndex){
		var record = grid.getStore().getAt(rowIndex);

		Ext.Ajax.request({
			url: this.controllerUrl + 'removecondition',
			method: 'post',
			scope:this,
			params:{'id':record.get('id')},
			success: function(response, request) {
				response =  Ext.JSON.decode(response.responseText);
				if(response.success){
					grid.getStore().remove(record);
					this.fireEvent('dataChanged');
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
	 * Show Condition editor window
	 * @param recotdId
	 */
	showEditWindow: function(recordId){
		var me = this;
		var win = Ext.create('app.report.filter.conditionWindow',{
			recordId:recordId,
			controllerUrl:this.controllerUrl
		});
		win.on('dataSaved',function(){
			me.dataStore.load();
			me.fireEvent('dataChanged');
		},this);
		win.show();
	}
});
/**
 *
 * @event dataChanged
 *
 */
Ext.define('app.report.filter.Filters',{
	extend:'Ext.grid.Panel',
	controllerUrl:'',
	viewConfig:{
		stripeRows:false
	},
	frame: false,
	loadMask:true,
	columnLines: true,
	scrollable:true,
	canEdit:false,
	canDelete:false,
	initComponent:function(){
		this.store = Ext.create('Ext.data.Store', {
			model: 'app.report.filter.FiltersModel',
			proxy: {
				type: 'ajax',
				url:this.controllerUrl +  'filterslist',
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
				property : 'id',
				direction: 'DESC'
			}]
		});


		this.columns = [
			{
				text:appLang.OBJECT,
				dataIndex:'object',
				flex:1
			},{
				text:appLang.FIELD,
				dataIndex:'field',
				width:100
			},{
				text:appLang.FILTERS_TYPE,
				dataIndex:'type',
				width:100
			}
		];

		this.columns.push({
			xtype:'actioncolumn',
			width:30,
			items:[{
				iconCls:'deleteIcon',
				scope:this,
				width:30,
				tooltip:appLang.DELETE_ITEM,
				handler:this.removeRecord
			}]
		});

		this.tbar = [
			{
				text:appLang.ADD_ITEM,
				scope:this,
				handler:function(){
					this.showEditWindow(null);
				}
			}
		];

		this.callParent();

	},
	/**
	 * Remove Filter Action
	 * @param grid
	 * @param rowIndex
	 * @param colIndex
	 */
	removeRecord:function(grid, rowIndex, colIndex){

	},
	/**
	 * Show Filter editor window
	 * @param recotdId
	 */
	showEditWindow: function(recotdId){

	}
});

/**
 * @event dataSaved
 */
Ext.define('app.report.filter.conditionWindow',{

	extend:'Ext.Window',

	recordId:null,
	dataForm:null,

	constructor: function(config) {
		config = Ext.apply({
			modal: true,
			layout:'fit',
			width: 380,
			height:250,
			closeAction: 'destroy',
			title:appLang.CONDITION
		}, config || {});
		this.callParent(arguments);
	},

	initComponent:function(){


		this.fieldObject = Ext.create('Ext.form.field.ComboBox',{
			xtype:'combo',
			name:'object',
			fieldLabel:appLang.OBJECT,
			queryMode:'local',
			displayField:'title',
			valueField:'id',
			triggerAction: 'all',
			forceSelection:true,
			allowBlank:false,
			listConfig:{
				getInnerTpl: function() {
					return '<tpl for="."><b>{id}</b> - {title}</tpl>';
				}
			},
			store:Ext.create('Ext.data.Store',{
				model:'app.comboStringModel',
				remoteSort:false,
				autoLoad:true,
				proxy: {
					url:this.controllerUrl+'objectlist',
					type: 'ajax',
					simpleSortMode: true,
					reader: {
						type: 'json',
						rootProperty: 'data',
						idProperty: 'id'
					}
				},
				sorters: [{
					property : 'title',
					direction: 'ASC'
				}]
			}),
			listeners:{
				change:function(field){
					this.fieldField.getStore().proxy.setExtraParam('object' , field.getValue());
					this.fieldField.getStore().load();
				},
				scope:this
			}
		});

		this.fieldField = Ext.create('Ext.form.field.ComboBox',{
			xtype:'combo',
			name:'field',
			fieldLabel:appLang.FIELD,
			queryMode:'local',
			displayField:'title',
			valueField:'id',
			triggerAction: 'all',
			forceSelection:true,
			allowBlank:false,
			listConfig:{
				getInnerTpl: function() {
					return '<tpl for="."><b>{id}</b> - {title}</tpl>';
				}
			},
			store:Ext.create('Ext.data.Store',{
				model:'app.comboStringModel',
				remoteSort:false,
				proxy: {
					url:this.controllerUrl+'fieldlist',
					type: 'ajax',
					simpleSortMode: true,
					reader: {
						type: 'json',
						rootProperty: 'data',
						idProperty: 'id'
					}
				},
				sorters: [{
					property : 'title',
					direction: 'ASC'
				}]
			})
		});

		this.fieldOperator = Ext.create('Ext.form.field.ComboBox',{
			xtype:'combo',
			name:'operator',
			fieldLabel:appLang.OPERATOR,
			queryMode:'local',
			displayField:'title',
			valueField:'id',
			triggerAction: 'all',
			forceSelection:true,
			allowBlank:false,
			store:Ext.create('Ext.data.Store',{
				model:'app.comboStringModel',
				remoteSort:false,
				autoLoad:true,
				proxy: {
					url:this.controllerUrl+'operatorlist',
					type: 'ajax',
					simpleSortMode: true,
					reader: {
						type: 'json',
						rootProperty: 'data',
						idProperty: 'id'
					}
				}
			}),
			listeners:{
				change:function(field){
					this.operatorSelected(field.getValue());
				},
				scope:this
			}
		});


		this.dataForm = Ext.create('Ext.form.Panel',{
			bodyPadding:5,
			bodyCls:'formBody',
			fieldDefaults:{
				labelWidth:90,
				anchor:'100%'
			},
			items:[
				{
					xtype:'hidden',
					name:'id',
					value:-1
				},
				this.fieldObject,
				this.fieldField,
				this.fieldOperator,
				{
					xtype:'textfield',
					fieldLabel:appLang.VALUE,
					allowBlank:false,
					name:'value'
				},{
					xtype:'textfield',
					fieldLabel:appLang.VALUE +'2',
					name:'value2',
					allowBlank:true,
					hidden:true
				}
			]
		});
		this.items = [this.dataForm];
		this.buttons = [
			{
				text:appLang.CANCEL,
				handler:this.close,
				scope:this
			},{
				text:appLang.SAVE,
				handler:this.saveData,
				scope:this
			}
		];

		this.callParent(arguments);


		if(this.recordId !==null){
			this.dataForm.getForm().load({
				url:this.controllerUrl + 'loadcondition',
				waitMsg:appLang.LOADING,
				params:{'id':this.recordId},
				success: function(form, action){
					if(!action.result.success){
						Ext.Msg.alert(appLang.MESSAGE, action.result.msg);
					}
				},
				failure: app.formFailure
			});
		}
	},
	/**
	 * On Operator selected
	 * @param operator
	 */
	operatorSelected:function(operator){
		this.dataForm.getForm().findField('value').show();
		this.dataForm.getForm().findField('value').allowBlank = false;
		this.dataForm.getForm().findField('value2').allowBlank = true;
		this.dataForm.getForm().findField('value2').hide();
		this.dataForm.getForm().findField('object').show();
		this.dataForm.getForm().findField('field').show();
		this.dataForm.getForm().findField('object').allowBlank = false;
		this.dataForm.getForm().findField('field').allowBlank = false;


		switch(operator){
			case 'BETWEEN' :
			case 'NOT_BETWEEN':
				this.dataForm.getForm().findField('value2').show();
				this.dataForm.getForm().findField('value2').allowBlank = false;
				break;
			case 'IS_NULL' :
			case 'IS_NOT_NULL':
				this.dataForm.getForm().findField('value').hide();
				this.dataForm.getForm().findField('value').allowBlank = true;
				break;
			case 'custom':
				this.dataForm.getForm().findField('object').hide();
				this.dataForm.getForm().findField('field').hide();
				this.dataForm.getForm().findField('object').allowBlank = true;
				this.dataForm.getForm().findField('field').allowBlank = true;
				break;
		}
	},
	saveData:function(){
		this.dataForm.getForm().submit({
			clientValidation: true,
			waitMsg:appLang.SAVING,
			method:'post',
			scope:this,
			url:this.controllerUrl + 'savecondition',
			params:{'objectName':this.objectName,'objectField':this.fieldName},
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
	}
});