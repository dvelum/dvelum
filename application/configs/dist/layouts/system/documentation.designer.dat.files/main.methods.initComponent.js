this.baseUrl = '';
this.controllerUrl = '';

this.sysConfiguration = null;
this.addDesignerItems(); 

this.callParent();
this.visibilityConfig  = {};
		
this.childObjects.center.on('tabchange',function(tabPanel, newCard, oldCard, eOpts ){
  if(newCard.infoLoaded){
    newCard.showInfo();
  }
});