<?php
/**
 * Storage interface
 * @package Store
 * @author Kirill Egorov 2011
 */
interface Store_Interface{
    /**
     * Store value
     * @param string $key
     * @param mixed $val
     * @return void
     */
    public function set($key,$val);
    /**
     * Get stored value by key
     * @param string $key
     * @return mixed
     */
    public function get($key);
    /**
     * Check if key exists
     * @param string $key
     * @return boolean
     */
    public function keyExists($key);
    /**
     * Remove data from storage
     * @param sting $key
     * @return void
     */ 
    public function remove($key);
    /**
     * Clear storage.(Remove data)
     */
    public function clear();
    /**
     * Get all storage data
     * @return array
     */
    public function getData();
    /**
     * Get records count
     * @return integer
     */
    public function getCount();
}