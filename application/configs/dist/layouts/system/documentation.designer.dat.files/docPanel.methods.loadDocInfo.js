var me = this;
me.mask(appLang.LOADING);
Ext.Ajax.request({
    url: this.controllerUrl + 'info',
    method: 'post',
    params:{
	  'fileHid':this.fileHid,
    },
    success: function(response, request) {
      response =  Ext.JSON.decode(response.responseText);
      if(response.success){
          me.fireEvent('dataLoaded' , response.data);
          me.infoLoaded = true;
          me.classInfo = response.data;
          me.showInfo();
          if(callback){
            callback();
          }
      } else {
          Ext.Msg.alert(appLang.MESSAGE , response.msg);
      }
      me.unmask();
    },
    failure:function() {	
	  me.unmask();
	  app.ajaxFailure(arguments);
    }
});