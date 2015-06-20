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
 * Config_File abstract class - an adapter implementing 
 * configurations, which use a file as a storage
 * @author Kirill Egorov
 * @abstract
 * @package Config
 */
 abstract class Config_File extends Config_Abstract
 {
     protected $writePath;
     public function __construct($name , $autoRead = true)
     {
         parent::__construct($name);
         $this->writePath = $name;
         /*
          * Read config from file
          */
         if($autoRead)
             $this->_data = $this->_readFile($name);
         else
         	 $this->_data = array();
     }
     
    /**
	 * File Read method, which is to be invoked within a certain adapter
	 * @param string $name - configuration identifier, file path * @return array
	 */
     abstract protected function _readFile($name);
     
    /**
	 * Data Save method, which is to be invoked within a certain
	 * adapter
	 * @return boolean success
	 */
     abstract public function save();

     /**
      * Save save path
      * @param $path
      */
     public function setWritePath($path){
         $this->writePath = writePath;
     }

     /**
      * Get save path
      * @return string
      */
     public function getWritePath(){
         return $this->writePath;
     }

}
