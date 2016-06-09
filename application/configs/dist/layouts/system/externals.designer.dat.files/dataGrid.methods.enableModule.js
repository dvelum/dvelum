var me = this;
Ext.Ajax.request({
  url: '[%wroot%][%admp%][%-%]externals[%-%]enable',
  method: "post",
  scope:this,
  params:{
    id: record.get("id")
  },
  success: function(response, request) {
    response =  Ext.JSON.decode(response.responseText);
    if(response.success){
      me.getStore().load();
      me.buildMap();
    }else{
      Ext.Msg.alert(appLang.MESSAGE , response.msg);
    }
  },
  failure:function(){
    Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
  }
});