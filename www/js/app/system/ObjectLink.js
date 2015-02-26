Ext.ns('app.objectLink');

/**
 *
 *
 * @event completeEdit
 *
 * @event completeEdit
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
	initComponent:function(){

		var  me = this;
		var fieldClass = 'Ext.form.field.Text';

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

		this.dataFieldLabel = Ext.create('Ext.form.field.Display',{
			anchor:"100%",
			flex:1,
			value:"...",
			cls:'d_objectLink_input',
			listeners : {
				afterrender:{
					fn:function(cmp){
						cmp.getEl().on('click',me.showSelectionWindow,me);
					},
					scope:this
				}
			}
		});

		this.triggerButton = Ext.create('Ext.button.Button',{
			iconCls:'editIcon2',
			scope:me,
			text:appLang.SELECT,
			tooltip:appLang.SELECT,
			handler:me.showSelectionWindow,
			scope:this,
		});

		this.removeButton = Ext.create('Ext.button.Button',{
			iconCls:'deleteIcon',
			tooltip:appLang.RESET,
			scope:me,
			handler:function(){
				me.setValue("");
			}
		});

		if(this.hideId){
			this.layout = 'hbox';
		}

		var valueContainer = {
			anchor:"100%",
			xtype:'fieldcontainer',
			layout: 'hbox',
			items:[this.dataField , this.triggerButton , this.removeButton]
		};

		this.items = [this.dataFieldLabel , valueContainer];


		this.callParent();

	},
	showSelectionWindow:function(){
		var win = Ext.create('app.objectLink.SelectWindow', {
			width:600,
			height:500,
			selectMode:true,
			objectName:this.objectName,
			controllerUrl:this.controllerUrl + 'linkedlist',
			title:this.fieldLabel
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
			params:{
				object:this.objectName,
				id:curValue
			},
			scope:this,
			success: function(response, request) {
				response =  Ext.JSON.decode(response.responseText);
				if(!response.success){
					Ext.Msg.alert(appLang.MESSAGE , response.msg);
				} else{
					me.dataFieldLabel.setValue(response.data.title);
					me.forceComponentLayout();
				}
			},
			failure:function(){
				me.dataFieldLabel.setText('');
				app.ajaxFailure(arguments);
			}
		});
	}
});


Ext.define('app.objectLink.SelectWindow',{
	extend:'app.selectWindow',
	controllerUrl:'?',
	objectName:'',
	fieldName:'',
	singleSelect:true,
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
					rootProperty: 'data',
					totalProperty: 'count',
					idProperty: 'id'
				},
				startParam:'pager[start]',
				limitParam:'pager[limit]',
				sortParam:'pager[sort]',
				directionParam:'pager[dir]',
				extraParams:{
					'object':this.objectName
				},
				simpleSortMode: true
			},
			autoLoad:true,
			pageSize: 25,
			remoteSort: true,
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
			title:this.fieldLabel
		});
		win.on('itemSelected',function(record){
			this.addRecord(record);
			this.fireEvent('completeEdit');
		},this);
		win.show();
		app.checkSize(win);
	}
});
