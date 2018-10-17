<?php

/**
 * Class Ext_Grid_Filtersfeature
 * Deprecated class used for DVelum 0.9.x projects import
 * @deprecated
 */
class Ext_Grid_Filtersfeature extends Ext_Object
{
    protected $_filters = array();
    /**
     * Get filters list
     * @return array
     */
    public function getFilters()
    {
        return $this->_filters;
    }
    /**
     * Add filter
     * @param string $code
     * @param Ext_Grid_Filter $object
     * @return boolean
     */
    public function addFilter($code , Ext_Grid_Filter $object)
    {
        if($this->filterExists($code))
            return false;

        $this->_filters[$code] = $object;

        return true;
    }
    /**
     * Update filter
     * @param string $code
     * @param Ext_Grid_Filter $object
     * @return boolean
     */
    public function setFilter($code , Ext_Grid_Filter $object)
    {
        $this->_filters[$code] = $object;
        return true;
    }
    /**
     * Get filter object
     * @param string $code
     * @return Ext_Grid_Filter
     */
    public function getFilter($code)
    {
        return $this->_filters[$code];
    }
    /**
     * Remove filter
     * @param string $code
     */
    public function removeFilter($code)
    {
        unset($this->_filters[$code]);
    }
    /**
     * Check if filter exists
     * @param string $code
     * @return boolean
     */
    public function filterExists($code)
    {
        return isset($this->_filters[$code]);
    }

    public function __toString()
    {
        $this->_config->setXType('');
        $this->_config->setFType('filters');
        $this->_config->filters = "[\n\t".Utils_String::addIndent(implode(",\n",array_values($this->_filters)),2)."\n]";
        return parent::__toString();
    }
}