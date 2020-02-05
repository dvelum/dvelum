me = this;
Ext.Ajax.request({
  url: '[%wroot%][%admp%][%-%]externals[%-%]buildmap',
  method: "post",
  scope:this,
  success: function(response, request) {
    response =  Ext.JSON.decode(response.responseText);
    if(response.success){
      
    }else{
      Ext.Msg.alert(appLang.MESSAGE , response.msg);
    }
  },
  failure:function(){
    Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
  }
});