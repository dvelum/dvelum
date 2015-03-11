/**
 * @event select
 */
Ext.define('designer.iconField',{
	extend:'Ext.form.FieldContainer',
	alias:'widget.urlfield',
	triggerCls : 'urlTrigger',
	dataField:null,
	triggerButton:null,
	layout: 'hbox',
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
		        var win = Ext.create('designer.iconSelectorWindow', {
		            width:600,
		            height:400,
		            controllerUrl:this.controllerUrl,
		            title:desLang.images,
		            listeners: {
		                scope: me,
		                select:function(url){
		                	me.setValue(url);
		                	me.fireEvent('select');
		                }
		            }
		        });
		        win.show();
		 	}
		 });
		 this.items = [this.dataField , this.triggerButton];

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
	}
});