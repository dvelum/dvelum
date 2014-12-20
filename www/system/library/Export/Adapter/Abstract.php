<?php
abstract class Export_Adapter_Abstract
{
    /**
     * Filename
     * @var string
     */
    protected $_file;
    /**
     * Template data
     * @var array
     */
    protected $_data = array();
	/**
	 * Set template data
	 * @param $data
	 * @return void
	 */
	public function setData(array $data){
		$this->_data = $data;
	}	
	/**
	 * Set document filename (without extention)
	 * @param string $name
	 * @return void
	 */
	public function setFileName($name){
		$this->_file = $name;
	}
	/**
	 * Get document filename (within extention)
	 * @return string
	 */
	public function getFileName(){
		return  $this->_file;
	}	
	/**
	 * Stream contents
	 */
	abstract public function stream();	
	/**
	 * Save file
	 * @param string $path destination directory
	 * @return boolean
	 */
	abstract public function save($path);
}