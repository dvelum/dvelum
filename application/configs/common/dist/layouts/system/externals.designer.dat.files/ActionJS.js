Ext.onReady(function(){
  // Init permissions
  app.application.on("projectLoaded",function(module){
    if(Ext.isEmpty(module) || module === "Externals"){
      if(!Ext.isEmpty(appExternalsApplication.mainPanel)){
        appExternalsApplication.mainPanel.setCanEdit(app.permissions.canEdit("Externals"));
      }
    }
  });
});