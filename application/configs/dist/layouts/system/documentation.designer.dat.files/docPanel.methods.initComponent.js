this.infoLoaded = false;
// visibility configuration
this.visibilityCfg = Ext.apply({
  public:  true,
  private: false,
  protected: false,
  inherited:true,
  deprecated:false
}, this.visibilityCfg || {});

this.addDesignerItems();
if(this.canEdit){
  this.childObjects.editClassDesctiptionBtn.show();
}

this.callParent();