/**
 *
 * @event completeEdit
 *
 */
Ext.define('app.crud.orm.ObjectField',{
	extend:'Ext.form.FieldContainer',
	alias:'widget.objectfield',
	triggerCls : 'urlTrigger',
	dataField:null,
	triggerButton:null,
	layout: 'vbox',
	onlyController:false,
	controllerUrl:'',
	objectName:'',
	value:"",
	isVc:'',
	fieldLabel:'',
	initComponent:function(){
		 var  me = this;
		
		 this.dataField = Ext.create('Ext.form.field.Text',{
			 anchor:"100%",
			 readOnly :true,
			 name:this.name,
			 listeners:{
				 change:{
					 fn:this.getObjectTitle,
					 scope:this
				 }
			 }
		//	value:this.value,
		 });
		 
		 this.dataFieldLabel = Ext.create('Ext.form.field.Display',{
			 anchor:"100%",
			 value:"..."
		 });
		
		this.triggerButton = Ext.create('Ext.button.Button',{
			 iconCls:'searchIcon',
			 width:20,
			 scope:me,
			 tooltip:appLang.SELECT,
			 handler:function(){
		        var win = Ext.create('app.crud.orm.DataViewWindow', {
		            width:600,
		            height:500,
		            selectMode:true,
		            objectName:this.objectName,
		            controllerUrl:this.controllerUrl,
		            isVc:this.isVc,
		            title:this.fieldLabel,
		            listeners: {
		                scope: me,
		                select:function(record){
		                	me.setValue(record.get('id'));
		                	me.fireEvent('completeEdit');
		                }
		            }
		        });	
		        win.show();
		        app.checkSize(win);
		 	}
		 });
		
		this.removeButton = Ext.create('Ext.button.Button',{
			iconCls:'deleteIcon',
			width:20,
			tooltip:appLang.CLEAR,
			scope:me,
			handler:function(){
				me.setValue("");
			}
		});
		
		 var valueContainer = {
				 anchor:"100%",
				 xtype:'fieldcontainer',
				 layout: 'hbox',
				 items:[this.dataField , this.triggerButton , this.removeButton]
		 };
		
		
		 this.items = [this.dataFieldLabel , valueContainer];

		 this.callParent();
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
	},
	getObjectTitle:function(){
		var me = this;
		var curValue = me.getValue();
		
		if(curValue == "" || curValue == 0){
			me.dataFieldLabel.setValue('');
			return;
		}
		
		me.dataFieldLabel.getEl().mask(appLang.LOADING);
		
		Ext.Ajax.request({
			url:this.controllerUrl + 'objectTitle',
			method: 'post',
			params:{
				object:this.objectName,
				id:curValue
			},
			scope:this,
	 		success: function(response, request) {
	 			response =  Ext.JSON.decode(response.responseText);
	 			if(!response.success){
	 				me.dataFieldLabel.getEl().unmask();
	 				Ext.Msg.alert(appLang.MESSAGE , response.msg);
	 			} else{
	 				me.dataFieldLabel.getEl().unmask();
	 				me.dataFieldLabel.setValue(response.data.title);
	 				me.updateLayout();
	 			}
	 		},
	 		failure:function(){
	 			me.dataFieldLabel.getEl().unmask();
	 			app.ajaxFailure(arguments);
	 		}
		});
	}
});