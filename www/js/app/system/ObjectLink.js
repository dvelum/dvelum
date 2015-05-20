Ext.ns('app.objectLink');
/**
 *
 * @event completeEdit
 *
 *
 * @event change
 * @param fld
 *
 */
Ext.define('app.objectLink.Field',{
	extend:'Ext.form.FieldContainer',
	alias:'widget.objectlinkfield',
	triggerCls : 'urlTrigger',
	dataField:null,
	triggerButton:null,
	layout: 'vbox',
	controllerUrl:'?',
	objectName:'',
	value:"",
	hideId:true,
	name:'',
	fieldLabel:'',
	readOnly:false,
	/**
	 * Extra params for requests
	 * @property {Object}
	 */
	extraParams:null,

	constructor: function(config) {
		config = Ext.apply({
			extraParams:{}
		}, config || {});
		this.callParent(arguments);
	},

	initComponent:function(){

		var  me = this;
		var fieldClass = 'Ext.form.field.Text';
		var fieldFlex = 1;

		if(this.hideId){
			fieldClass = 'Ext.form.field.Hidden';
		}

		this.dataField = Ext.create(fieldClass,{
			anchor:"100%",
			readOnly :true,
			name:this.name,
			listeners:{
				focus:{
					fn:this.showSelectionWindow,
					scope:this
				},
				change:{
					fn:this.getObjectTitle,
					scope:this
				}
			}
		});

		this.dataFieldLabel = Ext.create('Ext.form.field.Text',{
			anchor:"100%",
			flex:1,
			value:"...",
			editable:false,
		//	cls:'d_objectLink_input',
			triggers: {
				select: {
					cls: 'x-form-search-trigger',
					handler:me.showSelectionWindow,
					tooltip:appLang.SELECT,
					scope:this
				},
				clear: {
					cls: 'x-form-clear-trigger',
					tooltip:appLang.RESET,
					handler:function(){
						me.setValue("");
					},
					scope:this
				}
			}
		});

		if(this.hideId){
			this.layout ={
				type: 'hbox',
				pack: 'center',
				align: 'middle'
			};
		}

		this.items = [this.dataFieldLabel , this.dataField ];

		this.callParent();

		this.on('disable' , function(){
			this.updateViewState();
		},this);

		this.on('enable' , function(){
			this.updateViewState();
		},this);

		this.updateViewState();
	},
	showSelectionWindow:function(){

		if(this.readOnly || this.disabled){
			return false;
		}

		var win = Ext.create('app.objectLink.SelectWindow', {
			width:600,
			height:500,
			selectMode:true,
			objectName:this.objectName,
			controllerUrl:this.controllerUrl + 'linkedlist',
			title:this.fieldLabel,
			extraParams:this.extraParams
		});
		win.on('itemSelected',function(record){
			this.setValue(record.get('id'));
			this.fireEvent('completeEdit');
			win.close();
		},this);
		win.show();
		app.checkSize(win);
	},
	setValue:function(value){
		this.dataField.setValue(value);
		this.fireEvent('change' , this);
	},
	getValue:function(){
		return this.dataField.getValue();
	},
	reset:function(){
		this.dataField.reset();
		this.fireEvent('change' , this);
	},
	isValid:function(){
		return true;
	},
	getObjectTitle:function(){
		var me = this;
		var curValue = me.getValue();

		if(curValue == "" || curValue == 0){
			me.dataFieldLabel.setValue('');
			return;
		}

		me.dataFieldLabel.setValue(appLang.LOADING);

		Ext.Ajax.request({
			url:this.controllerUrl + 'otitle',
			method: 'post',
			params:Ext.apply({
				object:this.objectName,
				id:curValue
			},this.extraParams),
			scope:this,
			success: function(response, request) {
				response =  Ext.JSON.decode(response.responseText);
				if(!response.success){
					Ext.Msg.alert(appLang.MESSAGE , response.msg);
				} else{
					me.dataFieldLabel.setValue(response.data.title);
					me.updateLayout();
				}
			},
			failure:function(){
				me.dataFieldLabel.setText('');
				app.ajaxFailure(arguments);
			}
		});
	},
	/**
	 * Set request param
	 * @param string name
	 * @param string value
	 * @return void
	 */
	setExtraParam:function(name , value){
		this.extraParams[name] = value;
	},
	/**
	 * Sets the read only state of this field.
	 * @param Boolean readOnly
	 * @return void
	 */
	setReadOnly:function(readOnly){
		this.readOnly = readOnly;
		this.updateViewState();
	},
	updateViewState:function(){
		if(this.disabled){
			//this.triggerButton.hide();
			//this.removeButton.hide();
			this.dataField.hide();
			return;
		}
		else{
			this.dataField.enable();
			this.dataField.show();
			if(this.readOnly){
				//this.dataFieldLabel.setReadOnly(true);
				//this.triggerButton.hide();
				//this.removeButton.hide();
				this.dataField.setReadOnly(true);
			}else{
				//this.dataFieldLabel.setReadOnly(false);
				//this.dataField.setReadOnly(false);
				//this.triggerButton.show();
				//this.removeButton.show();
			}
		}
	}
});


Ext.define('app.objectLink.SelectWindow',{
	extend:'app.selectWindow',
	controllerUrl:'?',
	objectName:'',
	fieldName:'',
	singleSelect:true,
	/**
	 * Extra params for requests
	 * @property {Object}
	 */
	extraParams:null,

	constructor: function(config) {
		config = Ext.apply({
			extraParams:{}
		}, config || {});
		this.callParent(arguments);
	},

	initComponent:function(){

		this.dataStore =  Ext.create('Ext.data.Store',{
			fields:[
				{name:'id' , type:'integer'},
				{name:'title' , type:'string'},
				{name:'published' , type:'boolean'},
				{name:'deleted' , type:'boolean'}
			],
			proxy: {
				type: 'ajax',
				url: this.controllerUrl,
				reader: 'json',
				reader: {
					type: 'json',
					root: 'data',
					totalProperty: 'count',
					idProperty: 'id'
				},
				startParam:'pager[start]',
				limitParam:'pager[limit]',
				sortParam:'pager[sort]',
				directionParam:'pager[dir]',
				extraParams:Ext.apply({
					'object':this.objectName
				},this.extraParams),
				simpleSortMode: true
			},
			autoLoad:true,
			pageSize: 25,
			remoteSort: true
		});

		this.searchField = Ext.create('SearchPanel',{
			store:this.dataStore,
			local:false,
			fieldNames:['title']
		});


		this.dataPanel = Ext.create('Ext.grid.Panel',{
			viewConfig:{
				stripeRows:true
			},
			frame: false,
			loadMask:true,
			columnLines: true,
			autoScroll:true,
			store:this.dataStore,
			tbar:[
				'->' , this.searchField
			],
			bbar : Ext.create("Ext.PagingToolbar", {
				store : this.dataStore,
				displayInfo : true,
				displayMsg : appLang.DISPLAYING_RECORDS + " {0} - {1} " + appLang.OF + " {2}",
				emptyMsg : appLang.NO_RECORDS_TO_DISPLAY
			}),
			columns:[
				{
					dataIndex: 'published',
					text: appLang.STATUS,
					width:50,
					align:'center',
					renderer:function(value, metaData, record, rowIndex, colIndex, store){
						if(record.get('deleted')){
							metaData.attr = 'style="background-color:#000000;white-space:normal;"';
							return '<img src="'+app.wwwRoot+'i/system/trash.png" data-qtip="'+appLang.INSTANCE_DELETED+'" >';
						}else{
							return app.publishRenderer(value, metaData, record, rowIndex, colIndex, store);
						}
					}
				},
				{
					dataIndex:'title',
					text:appLang.TITLE,
					flex:1
				}
			]
		});

		this.callParent(arguments);
	},
	/**
	 * Set request param
	 * @param string name
	 * @param string value
	 * @return void
	 */
	setExtraParam:function(name , value){
		this.extraParams[name] = value;
	}
});

Ext.define('app.objectLink.Panel',{
	extend:'app.relatedGridPanel',
	alias:'widget.objectlinkpanel',
	name:'',
	objectName:'',
	controllerUrl:'',

	initComponent:function(){
		this.fieldName = this.name;
		this.callParent(arguments);
		this.on('addItemCall', this.showSelectWindow , this);
	},
	showSelectWindow:function(){
		var win = Ext.create('app.objectLink.SelectWindow', {
			width:600,
			height:500,
			selectMode:true,
			objectName:this.objectName,
			controllerUrl:this.controllerUrl + 'linkedlist',
			title:this.fieldLabel,
			extraParams:this.extraParams
		});
		win.on('itemSelected',function(record){
			this.addRecord(record);
			this.fireEvent('completeEdit');
		},this);
		win.show();
		app.checkSize(win);
	},
	/**
	 * Set request param
	 * @param string name
	 * @param string value
	 * @return void
	 */
	setExtraParam:function(name , value){
		this.extraParams[name] = value;
	}
});
