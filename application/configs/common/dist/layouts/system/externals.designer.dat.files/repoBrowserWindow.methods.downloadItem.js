var me = this;
var myEl = me.getEl();
myEl.mask(externalsLang.downloading);
Ext.Ajax.request({
  url: '[%wroot%][%admp%][%-%]externals[%-%]download',
  method: "post",
  scope:this,
  params:{
    repo: repo,
    app:app,
    version:vers
  },
  success: function(response, request) {
    response =  Ext.JSON.decode(response.responseText);
    if(response.success){
      me.fireEvent('downloaded');
    }else{
      Ext.Msg.alert(appLang.MESSAGE , response.msg);
    }
    myEl.unmask();
  },
  failure:function(){
    Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
    myEl.unmask();
  }
});