for(i in this.visibilityCfg){
  this.childObjects['showType_'+i].setChecked(this.visibilityCfg[i] , true);
}

var data = this.classInfo;
var type = data.itemType;

if(data.abstract){
  type =  'abstract '+ type;
}
var title = '<span class="docs-classType">' + type + '</span> ' + data.name;

if(data.implements.length){
  title+= ' <span class="docs-classType">implements</span> ' + data.implements;
}

if(data.extends.length){
  title+= ' <span class="docs-classType">extends</span> ' + data.extends;
}

this.childObjects.docPanelHeader.update(title);
this.childObjects.docPanelDescription.update(data.description);

if(!Ext.isEmpty(data.hierarchy)){
  var tree = this.childObjects.docPanel_hierarchy;
  
  //tree.getStore().removeAll();
  tree.setRootNode({
    id:0,
    text:'/',
    expanded:true,
    children:data.hierarchy
  });
  tree.show();
}

if(!Ext.isEmpty(data.properties)){
  this.showProperties(data.properties);
}

if(!Ext.isEmpty(data.methods)){
  this.showMethods(data.methods);
}