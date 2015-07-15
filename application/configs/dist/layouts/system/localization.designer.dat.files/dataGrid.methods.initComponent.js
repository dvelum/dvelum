this.addDesignerItems();
this.callParent();

this.getStore().on('update' , function(){
  this.hasChanges(true);
},this);
