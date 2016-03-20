Ext.onReady(function(){ 
  // Init permissions
  app.application.on("projectLoaded",function(module){
    if(Ext.isEmpty(module) || module === 'Filestorage'){
      appFilestorageRun.mainPanel.setPermissions(app.permissions.canEdit("Filestorage"), app.permissions.canDelete("Filestorage"));
    }
  });
});