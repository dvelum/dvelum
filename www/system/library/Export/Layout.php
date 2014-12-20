<?php
/**
 * @abstract
 */
abstract class Export_Layout
{
    /**
     * @var Export_Adapter_Abstract
     */
    protected $_adapter;
    /**
     * Document data
     * @var array
     */
    protected $_data;
    /**
     * Set document data 
     * @param array $data
     */
    public function setData(array $data)
    {
        $this->_data = $data;
    }
    /**
     * Set export adapter
     * @param Export_Adapter_Abstract $_adapter
     */
    public function setAdapter(Export_Adapter_Abstract $adapter)
    {
        $this->_adapter = $adapter;
    }
    /**
     * Build document
     */
    abstract public function doLayout();
}