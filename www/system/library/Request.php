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
 * Request wrapper
 * @author Kirill Egorov 2008
 */
class Request
{
    protected static $instance;
    protected static $_delimiter = '/';
    protected static $_wwwRoot = '/';
    protected static $_extension = '';
    protected static $_updatedPost = array();
    protected static $_updatedGet = array();
    protected $_path = array();
    protected $_uri;

    protected function __clone()
    {
    }

  /**
	 * Instantiate the object
	 * @return Request
	 */
   static public function getInstance()
   {
       if(!isset(self::$instance))
           self::$instance = new self();

       return self::$instance;
   }

  /**
	 * Set query string parameter delimiter
	 * For instance, “/” being set as a separator, the following query
	 * http://yoursite.com/controller/action will be interpreted
	 * as a parameterized request with two parameters
	 * 0 — controller 1 - action
	 * @param string $delimeter
	 * @return void
	 */
   static public function setDelimeter($delimeter)
   {
        self::$_delimiter = $delimeter;
   }

   /**
	  * Set postfix address extension
	  * (e.g. ".html" , ".xhtml" and the like)
	  * @param string $extension
	  * @return void
	  */
    static public function setExtension($extension)
    {
        self::$_extension = $extension;
    }

   /**
	  * Set www root
	  * @param string $root
	  */
    static public function setRoot($root)
    {
        self::$_wwwRoot = $root;
    }

    protected function __construct()
    {
        if(!isset($_SERVER['REQUEST_URI']))
            $_SERVER['REQUEST_URI'] = '/';

        $this->_parseUri($_SERVER['REQUEST_URI']);
        $this->_findPaths();
    }

    protected function _parseUri($string)
    {
        if(strpos($string , '?'))
            $string = substr($string , 0 , strpos($string , '?'));

        $string = str_ireplace(array(
                '.html' ,
                '.php' ,
                '.xml' ,
                '.phtml' ,
                '.json'
        ) , '' , $string);

        $this->_uri = preg_replace("/[^A-Za-z0-9_\.\-\/]/i" , '' , $string);
    }

  /**
	 * Set request URI
	 * @param string $uri
	 */
   public function setUri($uri)
   {
        $this->_parseUri($uri);
        $this->_findPaths();
   }

  /**
	 * Get cleared request URL
	 * @return string
	 */
   public function getUri()
   {
        return $this->_uri;
   }

  /**
	 * Scan request path items
	 */
   protected function _findPaths()
   {
     $this->_path = array();
     $uri = $this->_uri;

     $rootLen = strlen(self::$_wwwRoot);

	   if(substr($uri, 0 , $rootLen) === self::$_wwwRoot)
	     $uri = substr($uri, $rootLen);

     $array = explode(self::$_delimiter , $uri);
     for($i = 0, $sz = sizeof($array); $i < $sz; $i++)
         $this->_path[] = $array[$i];
    }

  /**
	 * Get request part by index
	 * The query string is divided into parts by the delimiter defined by the
	 * method Request::setDelimentr are indexed with  0
	 * @param integer $index — index of the part
	 * @return mixed string / false
	 */
   public function getPart($index)
   {
       if(isset($this->_path[$index]) && !empty($this->_path[$index]))
           return $this->_path[$index];
       else
           return false;
   }

    /**
     * Build system request URL
     * The method creates a string based on the defined parameter delimiter and
     * the parameter values array
     * @param array $paths — request parameters array
     * @return string — add postfix file extension
     */
    static public function url(array $paths , $useExstension = true)
    {
        $str = self::$_wwwRoot . implode(self::$_delimiter , $paths);

        if($useExstension)
            $str .= self::$_extension;

        return strtolower($str);
    }

  /**
	 * Get the list of sent files
	 * @return array
	 */
   static public function files()
   {
      if(!isset($_FILES) || empty($_FILES))
          return array();

      $result = array();

      if(empty($_FILES))
          return $result;

      foreach($_FILES as $key => $data)
      {
          if(!isset($data['name']))
              continue;

          if(!is_array($data['name']))
          {
              $result[$key] = $data;
          }
          else
          {
              foreach($data['name'] as $subkey => $subval)
              {
                  $result[$key][$subkey] = array(
                          'name' => $data['name'][$subkey] ,
                          'type' => $data['type'][$subkey] ,
                          'tmp_name' => $data['tmp_name'][$subkey] ,
                          'error' => $data['error'][$subkey] ,
                          'size' => $data['size'][$subkey]
                  );
              }
          }
      }
      return $result;
   }

  /**
	 * Get parameter transferred by the method $_GET
	 * @param string $name — parameter name
	 * @param string $type — the value type defining the way the data will be filtered.
	 * The ‘Filter’ chapter expands on the list of supported types. Here is the basic list:
	 * integer , boolean , float , string, cleaned_string , array и др.
	 * @param mixed $default — default value if the parameter is missing.
	 * @return mixed
	 */
   static public function get($name , $type , $default)
   {
        if(isset(self::$_updatedGet[$name]))
            return Filter::filterValue($type , self::$_updatedGet[$name]);

        if(!isset($_GET[$name]))
            return $default;
        else
            return Filter::filterValue($type , $_GET[$name]);
   }

  /**
	 * Get the parameter passed by $_POST method
	 * @param string $name — parameter name
	 * @param string $type —   the value type defining the way the data will be filtered.
	 * The ‘Filter’ chapter expands on the list of supported types. Here is the basic list:
	 * integer , boolean , float , string, cleaned_string , array и др.
	 * @param mixed $default — default value if  the parameter is missing.
	 * @return mixed
	 */
   static public function post($name , $type , $default)
   {
        if(isset(self::$_updatedPost[$name]))
            return Filter::filterValue($type , self::$_updatedPost[$name]);

        if(!isset($_POST[$name]))
            return $default;
        else
            return Filter::filterValue($type , $_POST[$name]);
    }

  /**
	 * Redefine $_POST parameter
	 * @param string $name — parameter name
	 * @param mixed $value — parameter value
	 */
   static public function updatePost($name , $value)
   {
       self::$_updatedPost[$name] = $value;
   }

   /**
    * Set POST data
    * @param array $data
    */
   static public function setPost(array $data){
   	self::$_updatedPost = $data;
   }

  /**
	 * Redefine $_GET parameter
	 * @param string $name — parameter name
	 * @param mixed $value — parameter value
	 */
   static public function updateGet($name , $value)
   {
       self::$_updatedGet[$name] = $value;
   }

  /**
	 * Get all parameters passed by the $_POST method in an array
	 * @return array
	 */
   static public function postArray()
   {
       return array_merge($_POST , self::$_updatedPost);
   }

  /**
	 * Get all parameters passed by the $_GET method in an array
	 * @return array
	 */
   static public function getArray()
   {
       return array_merge($_GET , self::$_updatedGet);
   }

  /**
	 * Check if request is sended by XMLHttpRequest
	 * @return boolean
	 */
   static public function isAjax()
   {
        if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')
            return true;
        else
            return false;
   }

  /**
	 * Get parameter transferred by the method $_SERVER
	 * @param string $name — parameter name
	 * @param string $type — the value type defining the way the data will be filtered.
	 * The ‘Filter’ chapter expands on the list of supported types. Here is the basic list:
	 * integer , boolean , float , string, cleaned_string , array и др.
	 * @param mixed $default — default value if the parameter is missing.
	 * @return mixed
	 */
   static public function server($name , $type , $default)
   {
        if(!isset($_SERVER[$name]))
            return $default;

        return Filter::filterValue($type , $_SERVER[$name]);
   }

  /**
	 * Check if any POST requests have been sent
	 * @return boolean
	 */
   static public function hasPost()
   {
        if(empty($_POST) && empty(self::$_updatedPost))
            return false;

        return true;
   }

   /**
    * Get application base url
    * @return string
    */
   static public function baseUrl()
   {
    	$protocol = 'http://';
    	if(strpos(strtolower($_SERVER['SERVER_PROTOCOL']), 'https')!==false)
    	    $protocol = 'https://';

    	return $protocol.$_SERVER['HTTP_HOST'].self::$_wwwRoot;
   }

   /**
    * Get web toot path
    * @return string
    */
   static public function wwwRoot()
   {
      return self::$_wwwRoot;
   }

   /**
    * Get data from Ext Grid Filters Feature
    * @param string $container
    * @param string $method
    */
   static public function extFilters($container = 'filterfeature' , $method = 'POST')
   {
     if($method == 'POST'){
       $filter = self::post($container, 'array', array());
     }else{
       $filter = self::get($container, 'array', array());
     }

     if(empty($filter))
       return array();

     $result = array();


     foreach ($filter as $data)
     {
       $type = $data['data']['type'];
       $value = $data['data']['value'];
       $field = $data['field'];

       switch($type)
       {
         case 'string' :    $result[] = new Db_Select_Filter($field , '%'.$value.'%' , Db_Select_Filter::LIKE);
                            break;


         case 'list' :      if(is_array($value))
                            {
                                $result[] = new Db_Select_Filter($field , $value , Db_Select_Filter::IN);
                                break;
                            }

                            if(strpos($value, ',')!==false)
                            {
                                $list = explode(',' , $value);
                                $result[] = new Db_Select_Filter($field , $list , Db_Select_Filter::IN);
                                break;
                            }

                            $result[] = new Db_Select_Filter($field , $value , Db_Select_Filter::EQ);
                            break;


          case 'boolean' :  $result[] = new Db_Select_Filter($field , Filter::filterValue(Filter::FILTER_BOOLEAN, $value), Db_Select_Filter::EQ);
                            break;


          case 'numeric' :  switch ($data['data']['comparison'])
                            {
                            	case 'ne' : $result[] = new Db_Select_Filter($field , $value , Db_Select_Filter::NOT);
                            	  break;
                            	case 'eq' : $result[] = new Db_Select_Filter($field , $value , Db_Select_Filter::EQ);
                            	  break;
                            	case 'lt' : $result[] = new Db_Select_Filter($field , $value , Db_Select_Filter::LT);
                            	  break;
                            	case 'gt' : $result[] = new Db_Select_Filter($field , $value , Db_Select_Filter::GT);
                            	  break;
                            }
                            break;

          case 'datetime':
                            $value = date('Y-m-d H:i:s',strtotime($value));
                            switch ($data['data']['comparison'])
                            {
                            	case 'ne' : $result[] = new Db_Select_Filter($field , $value , Db_Select_Filter::NOT);
                            	break;
                            	case 'eq' : $result[] = new Db_Select_Filter($field , $value , Db_Select_Filter::EQ);
                            	break;
                            	case 'lt' : $result[] = new Db_Select_Filter($field , $value , Db_Select_Filter::LT);
                            	break;
                            	case 'gt' : $result[] = new Db_Select_Filter($field , $value , Db_Select_Filter::GT);
                            	break;
                            }
                            break;
          case 'date' :

                            $value = date('Y-m-d',strtotime($value));
                            switch ($data['data']['comparison'])
                            {
                               case 'ne' : $result[] = new Db_Select_Filter($field , $value , Db_Select_Filter::NOT);
                                 break;
                               case 'eq' : $result[] = new Db_Select_Filter($field , $value , Db_Select_Filter::EQ);
                                 break;
                               case 'lt' : $result[] = new Db_Select_Filter($field , $value , Db_Select_Filter::LT);
                                 break;
                               case 'gt' : $result[] = new Db_Select_Filter($field , $value , Db_Select_Filter::GT);
                                 break;
                            }
                            break;
       }
     }
     return $result;
   }
}

