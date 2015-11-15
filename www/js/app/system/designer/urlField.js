/**
 * Url property editor
 *
 * @event select - Fires when URL is selected
 * @param string url
 */
Ext.define('designer.urlWindow',{
	extend:'Ext.Window',
	fileTree:null,
	actionsGrid:null,
	layout:'border',
	onlyController:false,
	controllerUrl:'',
	initComponent:function(){

		this.title = desLang.selectController;
		this.fileTree =  Ext.create('app.FilesystemTree',{
			region:'center',
			controllerUrl:app.createUrl([designer.controllerUrl ,'url','']),
			split: true
		});

		this.actionsGrid = Ext.create('Ext.grid.Panel',{
			region:'east',
			width:300,
			columnLines:true,
			split:true,
			store:Ext.create('Ext.data.Store',{
				extraParams:{
					controller:''
				},
				fields:[
					{name:'name' , type:'string'},
					{name:'comment' , type:'string'},
					{name:'code' , type:'string'},
					{name:'url' , type:'string'}
				],
				proxy:{
					url:this.controllerUrl,
					type:'ajax',
					reader:{
						type:'json',
						idProperty:'name',
						rootProperty:'data'
					}
				},
				autoLoad:false
			}),
			columns:[
				{
					text:desLang.action,
					dataIndex:'name',
					width:100
				},{
					text:desLang.description,
					flex:1,
					dataIndex:'comment',
					renderer:app.linesRenderer
				}
			]
		});


		var me = this;
		this.buttons = [
			{
				text:desLang.select,
				scope:me,
				handler:me.onSelect
			},{
				text:desLang.cancel,
				scope:me,
				handler:me.close
			}
		];

		this.items = [this.fileTree , this.actionsGrid];

		this.callParent();
		/*
		 * Do not show Actions
		 */
		if(this.onlyController){
			return;
		}

		this.fileTree.getSelectionModel().on('selectionchange',function(sm , records , options){
			var store = this.actionsGrid.getStore();

			if(!sm.hasSelection()){
				return;
			}

			store.removeAll();
			var rec = records[0];
			if(rec.get('leaf')){
				store.proxy.setExtraParam('controller' , rec.get('id'));
				store.load();
			}

		},this);

	},
	onSelect:function(){
		if(this.onlyController)
		{
			if(!this.fileTree.getSelectionModel().hasSelection()){
				return;
			}
			var selected = this.fileTree.getSelectionModel().getSelection()[0];
			this.fireEvent('select' , selected.get('url'));
			this.close();
			return;
		}

		var sm = this.actionsGrid.getSelectionModel();
		if(!sm.hasSelection()){
			Ext.Msg.alert(appLang.MESSAGE, desLang.selectAction);
			return;
		}

		var selection = sm.getSelection()[0];
		this.fireEvent('select' , selection.get('url'));
		this.close();
	}
});

/**
 *
 * @event select
 * @param {Ext.Window}
 */
Ext.define('designer.urlField',{
	extend:'Ext.form.field.Text',
	constructor:function(config){
		var me = this;
		config = Ext.apply({
			extraParams:{},
			triggers : {
				select:{
					hideOnReadOnly:true,
					cls: 'urlTriggerIcon',
					width:25,
					handler: function(field,trigger,e) {
						me.showSelectWindow();
						e.stopEvent();
					},
					scope:me
				}
			}
		}, config || {});
		this.callParent(arguments);
	},
	showSelectWindow:function(){
		var me = this;
		var win = Ext.create('designer.urlWindow', {
			width:600,
			height:400,
			modal:true,
			onlyController:this.onlyController,
			controllerUrl:this.controllerUrl,
			listeners: {
				scope: me,
				select:function(url){
					me.setValue(url);
					me.fireEvent('select' , url);
				}
			}
		});
		Ext.defer(function () {
			win.show().toFront();
		}, 50);
	}
});