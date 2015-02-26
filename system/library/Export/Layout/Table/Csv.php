<?php
class Export_Layout_Table_Csv extends Export_Layout{
    
    public function doLayout(){
        foreach ($this->_data as $row)
            $this->_adapter->addRow($row);
    }
}