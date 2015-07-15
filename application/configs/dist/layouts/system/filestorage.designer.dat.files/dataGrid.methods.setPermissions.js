this.canEdit = canEdit;
this.canDelete = canDelete;

if(canEdit){
  this.childObjects.uploadButton.show();
}else{
  this.childObjects.uploadButton.hide();
}

this.getView().refresh();