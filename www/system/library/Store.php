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
 * Store Factory 
 * @author Kirill Egorov 2010
 */
class Store
{
   const Local = 1;
   const Session = 2;
    
   /**
    * Store factory
    * @param int $type - const
    * @param string $name
    * @return Store_Interface or boolean false
    */
   static public function factory($type = Store::Local, $name = 'default')
   {
       switch($type){
           case self::Local : return Store_Local::getInstance($name);
               break;
           case self::Session : return Store_Session::getInstance($name);
               break;      
           default: return false;     
       }
   }
}