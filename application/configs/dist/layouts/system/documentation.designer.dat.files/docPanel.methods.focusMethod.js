if(!id){
  return;
}


  var el = this.childObjects.docPanelMethodContainer.getEl();
  el.select('div.docs-methodDescription').removeCls('selected'); 
  var els = el.select('div[methodid='+id+']'); 


if(els.getCount()){
  this.getTargetEl().scrollTo('top' , els.elements[0].offsetTop);
  els.addCls('selected');
}


