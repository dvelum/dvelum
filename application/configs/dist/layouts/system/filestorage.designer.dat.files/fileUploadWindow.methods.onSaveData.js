
var me = this;
this.childObjects.fileUploadWindow_form.getForm().submit({
      clientValidation: true,
      waitMsg:appLang.SAVING,
      method:"post",
      url:"[%wroot%][%admp%][%-%]filestorage[%-%]upload",
      params:{
     
    },
    success: function(form, action) {
          if(!action.result.success){
              Ext.Msg.alert(appLang.MESSAGE, action.result.msg);
          } else{
              me.fireEvent("dataSaved");
              me.close();
          }
    },
    failure: app.formFailure
});
    
            