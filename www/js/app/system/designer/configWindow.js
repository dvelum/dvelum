/**
 *
 * @event dataSaved
 */
Ext.define('designer.configWindow',{
	extend:'Ext.Window',
	controllerUrl:'',
	title:desLang.projectConfig,
	dataForm:null,
	layout:'fit',
	modal:true,
	width:500,
	height:600,
	includesGrid:null,
	langsGrid:null,
	resizable:false,
	langsCombo:null,

	initComponent:function(){

		this.includesGrid = Ext.create('Ext.grid.Panel',{
			hideLabel:true,
			height:230,
			title:desLang.includedFiles,
			tbar:[
				{
					iconCls:'plusIcon',
					text:desLang.addFile,
					handler:this.showAddFile,
					scope:this
				} ,{
					iconCls:'plusIcon',
					text:desLang.addProjectFile,
					handler:this.showAddProject,
					scope:this
				}
			],
			store: Ext.create('Ext.data.Store',{
				proxy:{
					type:'ajax',
					reader:{
						type:'json'
					}
				},
				fields:[
					{name:'file',type:'string'}
				],
				autoLoad:false
			}),
			columns:[
				{
					text:desLang.name,
					dataIndex:'file',
					flex:1
				},
				app.sortColumn()
			]
		});

		this.langsCombo = Ext.create('Ext.form.ComboBox', {
			selectOnFocus:true,
			editable:true,
			triggerAction: 'all',
			anchor:'100%',
			allowBlank:false,
			queryMode: 'local',
			store: Ext.create('Ext.data.Store',{
				proxy:{
					type:'ajax',
					reader:{
						type:'json'
					}
				},
				fields:[
					{name:'name',type:'string'}
				],
				autoLoad:false
			}),
			valueField: 'name',
			displayField: 'name',
			allowBlank:true
		});

		this.langsGrid = Ext.create('Ext.grid.Panel',{
			hideLabel:true,
			height:240,
			title:desLang.includedLangs,
			tbar:[
				this.langsCombo,
				{
					iconCls:'plusIcon',
					text:desLang.addLangFile,
					handler:this.addLang,
					scope:this
				}
			],
			store: Ext.create('Ext.data.Store',{
				proxy:{
					type:'ajax',
					reader:{
						type:'json'
					}
				},
				fields:[
					{name:'name',type:'string'}
				],
				autoLoad:false
			}),
			columns:[
				{
					text:desLang.name,
					dataIndex:'name',
					flex:1
				},
				app.sortColumn()
			]
		});


		this.dataForm = Ext.create('Ext.form.Panel',{
			bodyCls:'formBody',
			bodyPadding:5,
			fieldDefaults:{
				labelAlign:'left',
				anchor:'100%',
				labelWidth:180
			},
			defaults:{
				xtype:'textfield'
			},
			items:[
				{
					name:'namespace',
					fieldLabel:desLang.projectNamespace

				},{
					name:'runnamespace',
					fieldLabel:desLang.projectRunNamespace
				},
				this.includesGrid ,
				this.langsGrid
			]
		});

		this.items = [this.dataForm];
		this.buttons = [
			{
				text:desLang.save,
				scope:this,
				handler:this.saveAction
			},{
				text:desLang.close,
				scope:this,
				handler:this.close
			},
		];

		this.callParent(arguments);

		this.dataForm.load({
			url:this.controllerUrl +  '/project/loadconfig',
			scope:this,
			success:function(form, action){
				if(action.result.success){
					this.includesGrid.getStore().loadData(action.result.data.files);
					this.langsGrid.getStore().loadData(action.result.data.langs);
					this.langsCombo.getStore().loadData(action.result.data.langsList);
				}else{
					Ext.Msg.alert(appLang.MESSAGE, action.result.msg);
					this.close();
				}
			},
			failure:function(form, action){
				Ext.Msg.alert(appLang.MESSAGE, desLang.cantLoad);
				this.close();
			}
		});
	},
	showAddFile:function(){
		var win = Ext.create('app.filesystemWindow',{
			controllerUrl:this.controllerUrl +  '/project/'
		});

		win.on('fileSelected',function(file){
			var store = this.includesGrid.getStore();
			store.insert(store.getCount(), {
				file:file
			});
		},this);

		win.show();
	},
	showAddProject:function(){
		var win = Ext.create('app.filesystemWindow',{
			controllerUrl:this.controllerUrl +  '/project/',
			listAction:'projectlist'
		});

		win.on('fileSelected',function(file){
			var store = this.includesGrid.getStore();
			store.insert(store.getCount(), {
				file:file
			});
		},this);

		win.show();
	},
	addLang:function(){
		var lang = this.langsCombo.getValue();
		var langStore = this.langsGrid.getStore();
		var index = langStore.findExact('name' , lang);
		if(index ==-1){
			langStore.insert(langStore.getCount(), {name:lang});
		}
	},
	saveAction:function()
	{
		var me = this;
		var files = [];
		var langs = [];

		this.includesGrid.getStore().each(function(record){
			files.push(record.get('file'));
		});

		this.langsGrid.getStore().each(function(record){
			langs.push(record.get('name'));
		});

		this.dataForm.getForm().submit({
			clientValidation: true,
			waitMsg: appLang.SAVING,
			method:'post',
			scope:this,
			params:{
				'files[]' : files,
				'langs[]' : langs
			},
			url:this.controllerUrl +  '/project/setconfig',
			success: function(form, action) {
				if(!action.result.success){
					Ext.Msg.alert(appLang.MESSAGE, action.result.msg);
				} else{
					me.fireEvent('dataSaved');
					me.close();
				}
			},
			failure: app.formFailure
		});
	}
});