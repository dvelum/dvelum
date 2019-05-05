<?php return array (
  'namespace' => 'appExternalsComponents',
  'runnamespace' => 'appExternalsApplication',
  'files' => 
  array (
  ),
  'langs' => 
  array (
    0 => 'externals',
  ),
  'actionjs' => 'www/js/app/actions//system/externals.js',
  'actionJs' => 'Ext.onReady(function(){
  // Init permissions
  app.application.on("projectLoaded",function(module){
    if(Ext.isEmpty(module) || module === "Externals"){
      if(!Ext.isEmpty(appExternalsApplication.mainPanel)){
        appExternalsApplication.mainPanel.setCanEdit(app.permissions.canEdit("Externals"));
      }
    }
  });
});',
); 