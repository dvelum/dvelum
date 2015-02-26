<?php
class Ext_Component_Renderer_System_Image extends Ext_Component_Renderer
{
    public function __toString(){
        return 'function(v,m,r ){
                  if(v.length){
                    return "<img src=\""+v+"\"/>";
                  }else{
                    return "";
                  }
              }';
    }
}