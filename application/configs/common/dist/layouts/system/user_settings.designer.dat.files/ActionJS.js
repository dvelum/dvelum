Ext.onReady(function(){ 
  // Init permissions
  app.application.on("projectLoaded",function(module){
    if(Ext.isEmpty(module) || module === 'Settings'){
      if(!app.permissions.canEdit("Settings")){
         appSettingsApplication.mainForm.setReadOnly();
      }
      appSettingsApplication.mainForm.loadForms();
    }
  });
});