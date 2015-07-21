var tab = false;
var curItemId = path+name;

this.childObjects.center.items.each(function(item , index){
  if(item.itemId === curItemId){
    tab = item;
  }
});

if(!tab)
{
  var tab = Ext.create('appDocClasses.docPanel',{
    fileHid:fileHid,
    closable:true,
    title:name,
    itemId:curItemId,
    canEdit:this.canEdit,
    controllerUrl:this.controllerUrl,
    tooltip: path + '/' + name,
    visibilityCfg:this.visibilityConfig,
    listeners:{
      'visibilityChange':{
        fn:function(config){
          this.visibilityConfig = config;
          this.childObjects.center.items.each(function(item){
            item.setVisibility(config);
          },this);
        },
        scope:this
      }
    }
  });
  this.childObjects.center.add(tab);
  tab.loadDocInfo(function(){
    tab.focusMethod(methodId);
  });
  this.childObjects.center.setActiveTab(tab);
}else{
  this.childObjects.center.setActiveTab(tab);
  tab.focusMethod(methodId);
}