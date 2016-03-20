Ext.onReady(function(){ 
  // Init permissions
  app.application.on("projectLoaded",function(module){
    if(Ext.isEmpty(module) || module === 'User'){
      appUsersRun.mainPanel.showTabs(app.permissions.canView("User") ,  app.permissions.canDelete("User"));
    }
  });
});
