this.addDesignerItems();
this.callParent();

if(this.canEdit){
  this.childObjects.addUserBtn.show();
}

this.childObjects.userSearch.store = this.getStore();