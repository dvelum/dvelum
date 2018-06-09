<?php
class Ext_Component_Renderer_System_User_Check extends Ext_Component_Renderer
{
    public function __toString()
    {
        return 'function(value, metaData, record, rowIndex, colIndex, store){
                    var headerCt = this.getHeaderContainer(), column = headerCt.getHeaderAtIndex(colIndex);
                    if(record.get("g_" + column.dataIndex)){
                        metaData.tdCls = "disabledPermissions";
                        metaData.tdAttr = "data-qtip=\""+appLang.GROUP_PERMISSIONS+"\"";
                    }
                    return app.checkboxRenderer(value, metaData, record, rowIndex, colIndex, store);
               }';
    }
}