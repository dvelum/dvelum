Ext.define('app.crud.orm.logWindow',{
	extend:'Ext.Window',
	controllerUrl:'',
	width:600,
	height:600,
	closeAction:'destroy',
	layout:'fit',
	title:appLang.LOG,
	bodyPadding:5,
	scrollable:true,
	displayField:false,
	bodyCls:'formBody',
	initComponent:function(){

		this.displayField = Ext.create('Ext.form.Display',{
			name:'text'
		});

		this.items = [this.displayField];

		this.fileSelector = Ext.create('Ext.form.field.ComboBox',{
			allowBlank:false,
			editable:false,
			forceSelection:true,
			queryMode:'local',
			displayField:'id',
			valueField:'id',
			name:'file',
			store:Ext.create('Ext.data.Store',{
				fields:[
					{name:'id' , type:'string'}
				],
				autoLoad:true,
				proxy:{
					type:'ajax',
					url: this.controllerUrl + 'getlogfiles',
					reader:{
						rootProperty:'data',
						idProperty:'id'
					}
				},
				listeners:{
					load:{
						fn:function(store , records){
							this.fileSelector.setValue(records[0].get('id'));
						},
						scope:this
					}
				}
			}),
			listeners:{
				change:{
					fn: function(cmp , value){
						this.loadLog(value);
					},
					scope:this
				}
			}
		});
		this.tbar = [appLang.FILE+':' , this.fileSelector];

		this.callParent();
	},
	setText:function(text){
		this.displayField.setValue(text);
	},
	loadLog:function(filename){
		var me = this;
		Ext.Ajax.request({
			url: me.controllerUrl + 'getlog',
			method: 'post',
			params:{
				file:filename
			},
			success: function(response, request) {
				response =  Ext.JSON.decode(response.responseText);
				if(response.success){
					me.setText(response.data);
				}else{
					Ext.Msg.alert(appLang.ERROR, response.msg);
				}
			},
			failure:app.formFailure
		});
	}
});