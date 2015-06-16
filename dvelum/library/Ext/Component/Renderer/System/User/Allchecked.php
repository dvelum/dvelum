<?php
class Ext_Component_Renderer_System_User_Allchecked extends Ext_Component_Renderer
{
    public function __toString(){
        return 'function (value, metaData, record, rowIndex, colIndex, store) {
                var allChecked = me.checkPermissionsCol(record);
                if (allChecked)
                    return \'<img src="\' + app.wwwRoot + \'i/system/checked.gif">\';
                else
                    return \'<img src="\' + app.wwwRoot + \'i/system/unchecked.gif">\';
        }';
    }
}