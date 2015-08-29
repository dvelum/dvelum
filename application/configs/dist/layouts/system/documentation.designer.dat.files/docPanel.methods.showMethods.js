var html = '';

var vis = this.visibilityCfg;
var me = this;

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

  if(item.static){
    visibility = 'static ' + visibility;
  }
  
  if(item.final){
    visibility = 'final ' + visibility;
  }
  
  if(item.type){
    type = '<span class="type"> : ' + item.type + '</span>';
  }
  
  html+='<div class="docs-methodDescription" methodid="'+item.id+'">'
       +   '<div '+deprecated+'>'
       +      '<span class="visibility">'+visibility+'</span> <span class="name">' + item.name + '( <span class="docs-params">' + item.paramsList + '</span> )</span>' + type
       +   '</div>'
       +   '<div>'
       +     '<div class="description">';
  
  if(this.canEdit){
    html+='<a href="javascript:" class="methodEditor" data-id="'+index+'" data-qtip="'+appLang.EDIT+'"><img data-id="'+index+'" src="'+app.wwwRoot+'i/system/edit.png"></a>';
  }
  
  
  html +=        item.description
       +     '</div>';
  
  if(!Ext.isEmpty(item.params))
  {
    Ext.each(item.params , function(item){
      
      html+='<div class="docs-paramDescription">';
      
      if(item.isRef){
        html+='&';
      }
      
      
      if(item.optional){
        html+='<span class="docs-optional" data-qtip="' + sysdocsLang.optional + '">opt</span>';
      }
      
      if(!Ext.isEmpty(item.returnType)){
        html+=' : ' + item.returnType;
      }
      
      html+= '<span class="name"> $' + item.name;
      
      if(item.default !== null && item.optional){
       html+= ' <span class="value">= ' + item.default+'</span>';
      }
      
      html+= '</span>  '
          +     '<span class="description">'
          +         item.description
          +     '</span>'
          + '</div>';
    });
  }
  if(item.returnType.length){
    html+= '<span><b>return</b> ' + item.returnType + '</span>';
  }
  html+='</div>'
      +'</div>';
},this);

this.childObjects.docPanelMethodContainer.update(html);

if(this.canEdit)
{
  var el = this.childObjects.docPanelMethodContainer.getEl();
  var btns = el.query('.methodEditor' , false); 
  Ext.Array.each(btns, function(item){
    Ext.get(item).on('click', function( aEvent, aElement ){  
      me.editMethodDescription(parseInt(aElement.getAttribute('data-id')));   
    },me);
  },me);
}

var labels = this.childObjects.docPanelMethodContainer.getEl().query('.docs-methodDescription');
Ext.Array.each(labels, function(item){
    Ext.get(item).addClsOnOver('docs-over');
},me);