/**
 *
 * @event objectAdded
 * @param string name
 */
Ext.define('designer.addInstanceWindow',{
	extend:'Ext.Window',
	modal:true,
	layout:'fit',
	width:300,
	height:130,
	controllerUrl:'',
	parentObject:'',

	initComponent:function(){
		this.title = desLang.addInstance;

		this.objectsStore = Ext.create('Ext.data.Store',{
			autoLoad:true,
			fields:[{
				name:"name",
				type:"string"
			}],
			proxy:{
				url:this.controllerUrl + 'caninstantiate',
				reader:{
					idProperty:"name",
					rootProperty:"data",
					type:"json"
				},
				type:"ajax"
			}
		});

		this.dataForm = Ext.create('Ext.form.Panel',{
			bodyPadding:5,
			bodyCls:'formBody',
			fieldDefaults:{
				anchor:'100%',
				labelWidth:70,
				labelAlign:'right'
			},
			items:[
				{
					name:'name',
					fieldLabel:desLang.name,
					xtype:'textfield',
					vType:'alphanum',
					allowBlank:false
				},
				{
					fieldLabel:desLang.instanceOf,
					xtype:'combobox',
					forceSelection:true,
					allowBlank:false,
					displayField:'name',
					valueField:'name',
					store:this.objectsStore,
					queryMode:'local',
					name:'instance'
				}
			]
		});

		this.buttons = [
			{
				text:desLang.add,
				handler:this.addObject,
				scope:this
			},{
				text:desLang.cancel,
				handler:this.close,
				scope:this
			}
		];

		this.items = [this.dataForm];

		this.callParent();
	},
	/**
	 * Add object instance to the project
	 */
	addObject:function(){
		var me = this;
		this.dataForm.getForm().submit({
			clientValidation: true,
			waitMsg:appLang.SAVING,
			method:'post',
			url:this.controllerUrl + 'addinstance',
			params:{'parent':this.parentObject},
			success: function(form, action) {
				if(!action.result.success){
					Ext.Msg.alert(appLang.MESSAGE, action.result.msg);
				} else{
					me.fireEvent('objectAdded' , me.dataForm.getForm().findField('name'));
					me.close();
				}
			},
			failure: app.formFailure
		});
	}
});