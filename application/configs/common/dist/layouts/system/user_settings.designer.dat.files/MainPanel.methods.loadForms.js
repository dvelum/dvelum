var me = this;
me.mask(appLang.LOADING);
Ext.Ajax.request({
  url:'[%wroot%][%admp%][%-%]settings[%-%]loadData',
  method: 'post',
  success: function(response, request) {
    response =  Ext.JSON.decode(response.responseText);
    if(response.success){
      me.childObjects.userForm.getForm().loadRecord(
        Ext.create('Ext.data.Model', response.data.user)
      );
 	  me.childObjects.settingsForm.getForm().loadRecord(
        Ext.create('Ext.data.Model', response.data.settings)
      );
      me.unmask();
    }else{
      Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_CANT_LOAD);
    }
  },
  failure:function() {
    Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
  }
});