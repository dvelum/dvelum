this.addDesignerItems();
this.callParent();
this.getStore().on('update',function(){
	this.childObjects.saveIndividualPermissionsBtn.enable();
},this);