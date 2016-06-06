<?php
/*
 * DVelum project http://code.google.com/p/dvelum/ , http://dvelum.net
 * Copyright (C) 2011-2015  Kirill A Egorov
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
    protected static $_updatedPost = array();
    protected static $_updatedGet = array();
    protected $_path = array();
    protected $_uri;

    protected static $config = array(
        'delimiter' => '/',
        'extension' => '',
        'wwwRoot' => '/',
    );

    /**
     * Set configuration options
     * @param array $config
     */
    static public function setConfig(array $config)
    {
        static::$config = $config;
    }

    /**
     * Set configuration option value
     * @param $name
     * @param $value
     */
    static public function setConfigOption($name , $value)
    {
        static::$config[$name] = $value;
    }

    protected function __clone(){}

    /**
     * Instantiate the object
     * @param boolean $forceReload
     * @return Request
     */
    static public function getInstance($forceReload = false)
    {
       if(!isset(self::$instance) || $forceReload){
           self::$instance = new self();
       }

       return self::$instance;
    }

    /**
     * Set query string parameter delimiter
     * For instance, “/” being set as a separator, the following query
     * http://yoursite.com/controller/action will be interpreted
     * as a parameterized request with two parameters
     * 0 — controller 1 - action
     * @param string $delimiter
     * @return void
     */
    static public function setDelimiter($delimiter)
    {
        static::$config['delimiter'] = $delimiter;
    }

    protected function __construct()
    {
        if(!isset($_SERVER['REQUEST_URI'])) {
            $_SERVER['REQUEST_URI'] = '/';
        }

        $this->_parseUri($_SERVER['REQUEST_URI']);
        $this->_findPaths();
    }

    protected function _parseUri($string)
    {
        if(strpos($string , '?')) {
            $string = substr($string , 0 , strpos($string , '?'));
        }

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

        $rootLen = strlen(static::$config['wwwRoot']);

        if(substr($uri, 0 , $rootLen) === static::$config['wwwRoot']) {
            $uri = substr($uri, $rootLen);
        }

        $array = explode(static::$config['delimiter'] , $uri);

        for($i = 0, $sz = sizeof($array); $i < $sz; $i++) {
            $this->_path[] = $array[$i];
        }
    }

   /**
    * Get request part by index
    * The query string is divided into parts by the delimiter defined by the
    * method Request::setDelimiter are indexed with  0
    * @param integer $index — index of the part
    * @return mixed string / false
    */
    public function getPart($index)
    {
       if(isset($this->_path[$index]) && !empty($this->_path[$index])) {
           return $this->_path[$index];
       } else {
           return false;
       }
    }

    /**
     * Get request parts
     * The query string is divided into parts by the delimiter defined by the
     * method Request::setDelimiter are indexed with  0
     * @param integer $offset, optional default 0 - index to start from
     * @return array
     */
    public function getPathParts($offset = 0)
    {
        return array_slice($this->_path, $offset);
    }

    /**
     * Build system request URL
     * The method creates a string based on the defined parameter delimiter and
     * the parameter values array
     * @param array $paths — request parameters array
     * @param boolean $useExtension - add extension
     * @return string — add postfix file extension
     */
    static public function url(array $paths , $useExtension = true)
    {
        $str = static::$config['wwwRoot'] . implode(static::$config['delimiter'] , $paths);

        if($useExtension) {
            $str .= static::$config['extension'];
        }

        return strtolower($str);
    }

    /**
     * Get the list of sent files
     * @return array
     */
    static public function files()
    {
        if(!isset($_FILES) || empty($_FILES)) {
            return array();
        }

        $result = array();

        if(empty($_FILES)) {
            return $result;
        }

        foreach($_FILES as $key => $data)
        {
            if(!isset($data['name'])) {
                continue;
            }

            if(!is_array($data['name'])){
                $result[$key] = $data;
            } else {
                foreach($data['name'] as $subkey => $subval){
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
            {return Filter::filterValue($type , self::$_updatedGet[$name]);}

        if(!isset($_GET[$name]))
            {return $default;}
        else
            {return Filter::filterValue($type , $_GET[$name]);}
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
            {return Filter::filterValue($type , self::$_updatedPost[$name]);}

        if(!isset($_POST[$name]))
            {return $default;}
        else
            {return Filter::filterValue($type , $_POST[$name]);}
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
            {return true;}
        else
            {return false;}
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
            {return $default;}

        return Filter::filterValue($type , $_SERVER[$name]);
    }

    /**
     * Check if any POST requests have been sent
     * @return boolean
     */
    static public function hasPost()
    {
        if(empty($_POST) && empty(self::$_updatedPost))
            {return false;}

        return true;
    }

    /**
    * Get application base url
    * @return string
    */
   static public function baseUrl()
   {
        $protocol = 'http://';
        if(!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS'])!=='off'){
            $protocol = 'https://';
        }
        return $protocol.$_SERVER['HTTP_HOST'] . static::$config['wwwRoot'];
   }

   /**
    * Get web toot path
    * @return string
    */
   static public function wwwRoot()
   {
      return static::$config['wwwRoot'];
   }

   /**
    * Process Ext Filters
    * @param string $container
    * @param string $method
    * @return array
    */
    static public function extFilters($container = 'storefilter' , $method = 'POST')
    {
        $result = [];

        if($method == 'POST'){
            $filter = self::post($container, 'raw', []);
        }else{
            $filter = self::get($container, 'raw', []);
        }

        if(empty($filter))
            return [];

        if(is_string($filter))
            $filter = json_decode($filter , true);

        $operators = [
            'gt' => Db_Select_Filter::GT,
            'lt' => Db_Select_Filter::LT,
            'like' => Db_Select_Filter::LIKE,
            '=' => Db_Select_Filter::EQ,
            'eq'=> Db_Select_Filter::EQ,
            'on' => Db_Select_Filter::EQ,
            'in' => Db_Select_Filter::IN,
            'ne' => Db_Select_Filter::NOT
        ];

        foreach ($filter as $data)
        {
            if(!empty($data['operator']))
                $operator = $data['operator'];
            else
                $operator = Db_Select_Filter::EQ;
            $value = $data['value'];
            $field = $data['property'];

            if(!isset($operators[$operator])){
                continue;
            }

            if($operator == 'like'){
                $result[] = new Db_Select_Filter($field , $value.'%' ,$operators[$operator]);
            }else{
                $result[] = new Db_Select_Filter($field , $value ,$operators[$operator]);
            }
        }
        return $result;
    }

    /**
     * Check HTTP_SCHEME for https
     * @return bool
     */
    static public function isHttps()
    {
        static $scheme = false;
        if($scheme === false){
            $scheme = isset($_SERVER['HTTP_SCHEME']) ? $_SERVER['HTTP_SCHEME'] : (((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || 443 == $_SERVER['SERVER_PORT']) ? 'https' : 'http');
        }
        if($scheme ==='https'){
            return true;
        }else{
            return false;
        }
    }
}

