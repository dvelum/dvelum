var btn = this.childObjects.rebuildIndexBtn
var oldText = btn.getText();
btn.setText(' <img src="'+app.wwwRoot+'i/system/ajaxload.gif" height="14"> ');
Ext.Ajax.request({
  url: app.createUrl([app.admin ,'localization' , 'rebuildindex']),
  method: 'post',	
  timeout:240000,
  success: function(response, request) {
     response =  Ext.JSON.decode(response.responseText);
     if(!response.success){
        Ext.Msg.alert(appLang.MESSAGE, response.msg);
     }
     btn.setText(oldText);
  },
  failure:function(){
    Ext.Msg.alert(appLang.MESSAGE, appLang.CANT_EXEC);   
	btn.setText(oldText);
  }
});