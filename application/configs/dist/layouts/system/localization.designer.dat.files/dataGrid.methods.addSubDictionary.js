me = this;
Ext.MessageBox.prompt(appLang.MESSAGE , appLang.ENTER_DICTIONARY_NAME,function(btn , text){
	    if(btn !='ok'){
          return;
	    }
  
        Ext.Ajax.request({
          url:app.createUrl([app.admin , 'localization' , 'createsub']),
          method: 'post',
          params:{
            'name':text
          },
          scope:this,
          success: function(response, request) {
              response =  Ext.JSON.decode(response.responseText);
              if(!response.success){	 				
                  Ext.Msg.alert(appLang.MESSAGE , response.msg);
              } else{
                  
                  appLocalizationRun.langsStore.load();
              }
          },
          failure:app.ajaxFailure
      });
        
});