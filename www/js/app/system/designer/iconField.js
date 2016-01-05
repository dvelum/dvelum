/**
 * @event select
 */
Ext.define('designer.iconField',{
	alias:'widget.urlfield',
	controllerUrl:'',
	extend:'Ext.form.field.Text',
	updateEl:true,
	constructor:function(config){
		var me = this;
		config = Ext.apply({
			extraParams:{},
			triggers : {
				select:{
					hideOnReadOnly:true,
					cls: 'urlTriggerIcon',
					width:25,
					handler: function() {
						me.showSelectWindow();
					},
					scope:me
				}
			}
		}, config || {});
		this.callParent(arguments);
	},
	showSelectWindow:function(){
		var me = this;
		var win = Ext.create('designer.iconSelectorWindow', {
			width:600,
			height:400,
			controllerUrl:this.controllerUrl,
			title:desLang.images,
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