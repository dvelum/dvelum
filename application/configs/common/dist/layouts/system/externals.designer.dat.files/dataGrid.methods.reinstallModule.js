me = this;
Ext.Ajax.request({
  url: '[%wroot%][%admp%][%-%]externals[%-%]reinstall',
  method: "post",
  scope:this,
  params:{
    id: record.get("id")
  },
  success: function(response, request) {
    response =  Ext.JSON.decode(response.responseText);
    if(response.success){
      me.postInstall(record);
    }else{
      Ext.Msg.alert(appLang.MESSAGE , response.msg);
    }
  },
  failure:function(){
    Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
  }
});