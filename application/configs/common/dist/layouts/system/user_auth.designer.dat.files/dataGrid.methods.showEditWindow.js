
        var win = Ext.create("appUserAuthComponents.editWindow", {
                  dataItemId:id,
                  canDelete:this.canDelete,canEdit:this.canEdit
            });

            win.on("dataSaved",function(){
                this.getStore().load();
              win.close();},this);

            win.show();
    