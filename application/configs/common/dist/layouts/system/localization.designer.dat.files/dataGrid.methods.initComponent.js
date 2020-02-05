this.addDesignerItems();
this.callParent();

this.getStore().on('update' , function(){
  this.hasChanges(true);
},this);


this.childObjects.searchField.store = this.getStore();