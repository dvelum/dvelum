var html = '';
var me = this;

var vis = this.visibilityCfg;

Ext.each(data,function(item , index){
  
  var visType = item.visibility;
  
  if(item.deprecated && !vis.deprecated){
    return;
  }
  
  if(item.inherited && !vis.inherited){
    return;
  }
  
  if(!vis[visType]){
    return;
  }
  
  var deprecated = '';
  var type = '';
  var visibility = visType;
  
  if(item.deprecated){
    deprecated = 'class="docs_deprecated"';
  }
  
  if(item.const){
    visibility = '<span class="docs-const">const</span> ';
    type = '<span class="type"> = ' + item.constValue + ';</span>';
  }else{
    if(item.static){
      visibility = 'static ' + visibility;
    }
    
    if(item.type){
      type = '<span class="type"> : ' + item.type + '</span>';
    }
  }
  
  
  html+='<div class="docs-propertyDescription">'
       +   '<div '+deprecated+'>'
       +      '<span class="visibility">'+visibility+'</span> <span class="name">' + item.name + '</span>' + type
       +   '</div>'
       +   '<div>';
  
  
    if(this.canEdit){
    html+='<a href="javascript:" class="docPropertyEditor" data-id="'+index+'" data-qtip="'+appLang.EDIT+'"><img data-id="'+index+'" src="'+app.wwwRoot+'i/system/edit.png"></a>';
  }

  
  html +=       '<span class="description">' + item.description + '</span>'
       +   '</div>'
       +'</div>';
},this);

this.childObjects.docPanelPropertyContainer.update(html);

if(this.canEdit)
{
  var el = this.childObjects.docPanelPropertyContainer.getEl();
  var btns = el.query('.docPropertyEditor' , false); 
  Ext.Array.each(btns, function(item){
    Ext.get(item).on('click', function( aEvent, aElement ){  
      me.editPropertyDescription(parseInt(aElement.getAttribute('data-id')));   
    },me);
  },me);
}
var labels = this.childObjects.docPanelMethodContainer.getEl().query('.docs-propertyDescription');
Ext.Array.each(labels, function(item){
    Ext.get(item).addClsOnOver('docs-over');
},me);