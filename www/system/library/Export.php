<?php
class Export
{
    const CSV = 'Export_Adapter_Csv';
    const EXCEL = 'Export_Adapter_Excel';
    const PDF = 'Export_Adapter_Pdf';
    const DOC = 'Export_Adapter_Doc';
    const RTF = 'Export_Adapter_Rtf';
    
    /**
     * Factory method
     * @param string $adapter - const Export_Adapter subclass class
     * @param string $layout - Export_Layout subclass
     * @param array $data - document data
     * @return Export_Adapter_Abstract
     */
    static public function factory($adapter , $layout , array $data)
    {
		if(!is_subclass_of($layout , 'Export_Layout'))
		    trigger_error($layout . ' should be inherited from Export_Layout');
	
		if(!is_subclass_of($adapter , 'Export_Adapter_Abstract'))
		    trigger_error($layout . ' should be inherited from Export_Adapter_Abstract');
	
		$layout = new $layout();
		$adapter = new $adapter();
		$layout->setData($data);
		$layout->setAdapter($adapter);
		$layout->doLayout();
		return $adapter;
    }
}