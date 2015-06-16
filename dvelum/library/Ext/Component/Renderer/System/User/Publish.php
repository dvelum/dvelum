<?php
class Ext_Component_Renderer_System_User_Publish extends Ext_Component_Renderer
{
    public function __toString()
    {
        return 'function(value, metaData, record, rowIndex, colIndex, store) {
                        if (record.get("rc")){
                            return app.checkboxRenderer(value, metaData, record, rowIndex, colIndex, store);
                        }else{
                            return "-";
                        }
               }';
    }
}