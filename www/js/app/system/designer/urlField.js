Ext.define('designer.urlWindow',{
	extend:'Ext.Window',
	modal:true,
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
		this.addEvents(
		            /**
		             * @event select
		             * Fires when URL is selected
		             * @param string url
		             */
		            'select'
		 );

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

Ext.define('designer.urlField',{
	extend:'Ext.form.FieldContainer',
	mixins:{completeEdit:'Ext.Editor'},
	alias:'widget.urlfield',
	triggerCls : 'urlTrigger',
	dataField:null,
	triggerButton:null,
	layout: 'hbox',
	onlyController:false,
	controllerUrl:'',
	initComponent:function(){
		 var  me = this;

		 this.dataField = Ext.create('Ext.form.field.Text',{
			flex:1
		 });

		this.triggerButton = Ext.create('Ext.button.Button',{
			 iconCls:'urltriggerIcon',
			 width:25,
			 scope:me,
			 handler:function(){
				 var win = Ext.create('designer.urlWindow', {
			            width:600,
			            height:400,
			            onlyController:this.onlyController,
			            controllerUrl:this.controllerUrl,
			            listeners:{
			            	select:{
			            		fn:function(url){
			            			this.setValue(url);
						        	this.fireEvent('select');
			            		},
			            		scope:this
			            	}
			            }
			        }).show();
			 }
		});

		this.items = [this.dataField , this.triggerButton];
		this.callParent();
		this.addEvents(
				/**
				 * @param {Ext.Window}
				 */
				'select'
			);
	},
	setValue:function(value){
		this.dataField.setValue(value);
	},
	getValue:function(){
		return this.dataField.getValue();
	},
	reset:function(){
		this.dataField.reset();
	},
	isValid:function(){
		return true;
	}
});