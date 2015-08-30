<?php
/*
 * DVelum project http://code.google.com/p/dvelum/ , http://dvelum.net
 * Copyright (C) 2011-2012  Kirill A Egorov
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
/**
 * Config reader for arrays in files
 * @author Kirill Egorov 2010
 * @package Config
 * @subpackage File
 */
class Config_File_Array extends Config_File
{
	/**
	 * Main config path to apply
	 * @var mixed
	 */
	protected $_applyTo;
    /**
     * (non-PHPdoc)
     * @see library/Config/File#_readFile($data)
     */
    protected function _readFile($name)
    {   		
        return require $name;
    }
    /**
     * (non-PHPdoc)
     * @see Config_File::save()
     */
    public function save()
    {
		if(!empty($this->_applyTo) && file_exists($this->_applyTo))
		{
			$src = include $this->_applyTo;
			$data = [];
			foreach($this->_data as $k=>$v){
				if(!isset($src[$k]) || $src[$k]!=$v){
					$data[$k] = $v;
				}
			}
		}else{
			$data = $this->_data;
		}

    	if(file_exists($this->_name))
    	{
    		if(!is_writable($this->_name))
    			return false;
    	}
    	else
    	{
    		$dir = dirname($this->_name);
    		
    		if(!file_exists($dir))
    		{
    			if(!@mkdir($dir,0775,true))
    				return false;
    			
    		}
    		elseif(!is_writable($dir))
    		{
    			return false;	
    		}
    	}
        if(Utils::exportArray($this->_name, $data)!==false){
           Config::cache();              
           return true;     
        }   
        return false; 
    }
	/**
     * Create config
     * @param string $file - path to config
	 * @throws Exception
	 * @return boolean - success flag  
     */
    static public function create($file)
    {
		$dir = dirname($file);
		if(!file_exists($dir) && !@mkdir($dir,0755, true))
			throw new Exception('Cannot create '.$dir);

    	if(File::getExt($file)!=='.php')
    		throw new Exception('Invalid file name');

    	if(Utils::exportArray($file, array())!==false)
    	    return true;

    	return false;    
    }

	/**
	 * Set main config file.
	 * @param mixed $path
	 */
	public function setApplyTo($path){
		$this->_applyTo = $path;
	}
}