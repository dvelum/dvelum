
        this.canEdit = canEdit;
        if(canEdit){
          this.childObjects.addButton.show();
        }else{
          this.childObjects.addButton.hide();
        }
        this.getView().refresh();
    