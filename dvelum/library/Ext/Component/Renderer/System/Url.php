<?php
class Ext_Component_Renderer_System_Url extends Ext_Component_Renderer
{
    public function __toString(){
        return 'function(v){
            return "<a href=\""+v+"\" target=\"_blank\">"+v+"</a>";
        }';
    }
}