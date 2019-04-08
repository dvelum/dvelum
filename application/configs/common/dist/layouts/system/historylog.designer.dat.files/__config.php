<?php return array (
  'namespace' => 'appHistorylogComponents',
  'runnamespace' => 'appHistorylogApplication',
  'files' => 
  array (
  ),
  'langs' => 
  array (
  ),
  'actionjs' => 'www/js/app/actions//system/historylog.js',
  'actionJs' => 'Ext.onReady(function(){ 
  // Init permissions
  app.application.on("projectLoaded",function(module){
    if(Ext.isEmpty(module) || module === \'Historylog\'){
      
    }
  });
});',
); 