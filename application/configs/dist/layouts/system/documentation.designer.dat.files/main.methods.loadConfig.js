this.baseUrl = app.createUrl([app.admin , 'docs']);
this.controllerUrl = this.baseUrl;

var me = this;
		
var url = document.createElement('a');
url.href = window.location;
var parts = url.pathname.split('/');

var lang = '1';
var vers = '1';
var docsPos =  false;
Ext.each(parts , function(item , index){;
  if(item == 'docs'){
    docsPos = index;
  }
});

if(docsPos!==false){
  if(parts[docsPos + 1]){
    lang = parts[docsPos + 1];
  }
  if(parts[docsPos + 2]){
    vers = parts[docsPos + 2];
  }
}


Ext.Ajax.request({
  url:app.createUrl([this.controllerUrl, lang, vers, 'config']),
  method: 'post',
  success: function(response, request) {
      response =  Ext.JSON.decode(response.responseText);
      if(response.success){
           me.sysConfiguration = response.data;
           me.fireEvent('configLoaded');
      }else{
          Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_CANT_LOAD_BLOCKS_CONFIG);  
      }
 },
 failure:function() {
      Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);   
 }
});