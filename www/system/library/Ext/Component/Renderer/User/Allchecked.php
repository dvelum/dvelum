<?php
class Ext_Component_Renderer_User_Allchecked extends Ext_Component_Renderer
{
    public function __toString(){
        return 'function (value, metaData, record, rowIndex, colIndex, store) {
                        var allChecked = this.checkPermissionsCol(record);
                        if (allChecked)
                            return \'<img src="\' + app.wwwRoot + \'js/lib/extjs4/resources/themes/images/default/menu/checked.gif">\';
                        else
                            return \'<img src="\' + app.wwwRoot + \'js/lib/extjs4/resources/themes/images/default/menu/unchecked.gif">\';
        }';
    }
}