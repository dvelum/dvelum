<?php
declare(strict_types=1);
/*
 * DVelum project http://code.google.com/p/dvelum/ , https://github.com/k-samuel/dvelum , http://dvelum.net
 * Copyright (C) 2011-2016  Kirill A Egorov
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
namespace Dvelum\Config;
/**
 * Config\File abstract class - an adapter implementing
 * configurations, which use a file as a storage
 * @author Kirill Egorov
 * @abstract
 * @package Config
 */
 abstract class File extends Config
 {
     protected $writePath;
     
     public function __construct(string $name , bool $autoRead = true)
     {
         parent::__construct($name);

         $this->writePath = $name;
         /*
          * Read config from file
          */
         if($autoRead)
             $this->data = $this->readFile($name);
         else
         	 $this->data = array();
     }
     
    /**
	 * File Read method, which is to be invoked within a certain adapter
	 * @param string $name - configuration identifier, file path
     * @return array
	 */
     abstract protected function readFile(string $name) : array;
     
    /**
	 * Data Save method, which is to be invoked within a certain
	 * adapter
	 * @return boolean success
	 */
     abstract public function save() : bool;

     /**
      * Save save path
      * @param $path
      */
     public function setWritePath(string $path)
     {
         $this->writePath = writePath;
     }

     /**
      * Get save path
      * @return string
      */
     public function getWritePath() : string
     {
         return $this->writePath;
     }

}
