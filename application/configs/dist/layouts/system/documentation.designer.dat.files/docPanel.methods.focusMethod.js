if(!id){
  return;
}

var labels = this.childObjects.docPanelMethodContainer.getEl().query('.docs-methodDescription' , false);
Ext.Array.each(labels, function(item){
    Ext.get(item).removeCls('selected');
});


labels = this.childObjects.docPanelMethodContainer.getEl().query('div[methodid="'+id+'"]',false);
if(labels.length){
  this.getTargetEl().scrollTo('top' , labels[0].dom.offsetTop);
  Ext.get(labels[0]).addCls('selected');
}


