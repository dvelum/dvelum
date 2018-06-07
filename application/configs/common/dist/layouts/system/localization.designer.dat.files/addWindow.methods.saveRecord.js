var me = this;
var form = me.childObjects.dataForm.getForm();

form.submit({
  clientValidation: true,
  waitMsg:appLang.SAVING,
  method:'post',
  url:app.createUrl([app.admin , 'localization' , 'addrecord']),
  params:{'dictionary':me.dictionary},
  success: function(form, action) {	
   		if(!action.result.success){
   		 	Ext.Msg.alert(appLang.MESSAGE, action.result.msg);
   		} else{
   		 	me.fireEvent('dataSaved' , form.getFieldValues());		 
   		 	me.close();
   		}
  },
  failure: app.formFailure
});