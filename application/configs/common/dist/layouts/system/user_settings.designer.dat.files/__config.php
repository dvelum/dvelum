<?php return array (
  'namespace' => 'appSettingsComponents',
  'runnamespace' => 'appSettingsApplication',
  'files' => 
  array (
  ),
  'langs' => 
  array (
  ),
  'actionjs' => 'www/js/app/actions//system/user_settings.js',
  'actionJs' => 'Ext.onReady(function(){ 
  // Init permissions
  app.application.on("projectLoaded",function(module){
    if(Ext.isEmpty(module) || module === \'Settings\'){
      if(!app.permissions.canEdit("Settings")){
         appSettingsApplication.mainForm.setReadOnly();
      }
      appSettingsApplication.mainForm.loadForms();
    }
  });
});',
); 