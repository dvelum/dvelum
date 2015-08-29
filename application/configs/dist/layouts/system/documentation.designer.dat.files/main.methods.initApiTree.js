this.childObjects.apiTree = Ext.create('appDocClasses.apiTree',{
		  controllerUrl:this.controllerUrl
});
		
		
this.childObjects.apiTree.getSelectionModel().on('selectionchange',function(sm, selected, options){
  
  if(!sm.hasSelection()){
        return;
  }
    
  var rec = selected[0];
  if(!rec.get('isDir')){
     this.showDoc(rec.get('hid') , rec.get('path'), rec.get('name') , false);
  }
}, this);
  
  
this.childObjects.navigation.add(this.childObjects.apiTree); 