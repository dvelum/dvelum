
        /*
         * Here you can define application logic
         * To obtain info about current user access rights
         * you can use global scope JS vars canEdit , canDelete , canPublish
         * To access project elements, please use the namespace you defined in the config
         * For example: appUserAuthApplication.Panel or Ext.create("appUserAuthComponents.editWindow", {});
         */
         Ext.onReady(function(){
                // Init permissions
                app.application.on("projectLoaded",function(module){
                    if(Ext.isEmpty(module) || module === "User_Auth"){
                        if(!Ext.isEmpty(appUserAuthApplication.dataGrid)){
                          appUserAuthApplication.dataGrid.setCanEdit(app.permissions.canEdit("User_Auth"));
                          appUserAuthApplication.dataGrid.setCanDelete(app.permissions.canDelete("User_Auth"));
                           appUserAuthApplication.dataGrid.getView().refresh();
                        }
                    }
                });
          });