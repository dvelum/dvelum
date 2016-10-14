<?php
class Ext_Component_Renderer_System_User_Check_Publish extends Ext_Component_Renderer
{
    public function __toString()
    {
        return 'function(value, metaData, record, rowIndex, colIndex, store) {
                        if (record.get("rc")){
                            var headerCt = this.getHeaderContainer(), column = headerCt.getHeaderAtIndex(colIndex);
                            if(record.get("g_" + column.dataIndex)){
                                metaData.tdStyle = "background-color:#DCDCDC;";
                                metaData.tdAttr = "data-qtip=\""+appLang.GROUP_PERMISSIONS+"\"";
                            }
                            return app.checkboxRenderer(value, metaData, record, rowIndex, colIndex, store);
                        }else{
                            return "-";
                        }
               }';
    }
}