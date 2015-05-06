
  		/*
  		 * Here you can define application logic
  		 * To obtain info about current user access rights
  		 * you can use global scope JS vars canEdit , canDelete , canPublish
  		 * To access project elements, please use the namespace you defined in the config
  		 * For example: appSysdocsLocalizationRun.Panel or Ext.create("appSysdocsLocalizationClasses.editWindow", {});
  		 */
        
  		function showSysdocsLocalizationEditWindow(id){
  		      Ext.create("appSysdocsLocalizationClasses.editWindow", {
  		          dataItemId:id,
  		          canDelete:canDelete,
  		          canEdit:canEdit
  		      }).show();
  		}
        
  		Ext.onReady(function(){
          
  		});