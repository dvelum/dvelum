<?php
/**
 *  DVelum project http://code.google.com/p/dvelum/ , https://github.com/k-samuel/dvelum , http://dvelum.net
 *  Copyright (C) 2011-2017  Kirill Yegorov
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */
declare(strict_types=1);

namespace Dvelum;

use Dvelum\Config\ConfigInterface;

/**
 * Request wrapper
 * @author Kirill Yegorov 2008
 * @package Dvelum
 */
class Request
{
    /**
     * @var ConfigInterface $config
     */
    protected $config;

    /**
     * @var string $uri
     */
    protected $uri;

    /**
     * Uri parts
     * @var array
     */
    protected $parts = [];

    protected $updatedGet = [];
    protected $updatedPost = [];

    /**
     * @return Request
     */
    static public function factory()
    {
        static $instance = null;

        if(empty($instance)){
            $instance = new static();
        }

        return $instance;
    }

    private function __construct()
    {
        if(!isset($_SERVER['REQUEST_URI'])) {
            $_SERVER['REQUEST_URI'] = '/';
        }
        $this->uri = $this->parseUri($_SERVER['REQUEST_URI']);
        $this->parts = $this->detectParts($this->uri);
    }

    protected function parseUri(string $string) : string
    {
        if(strpos($string , '?')!==false) {
            $string = substr($string , 0 , strpos($string , '?'));
        }

        $string = str_ireplace(array(
            '.html' ,
            '.php' ,
            '.xml' ,
            '.phtml' ,
            '.json'
        ) , '' , $string);

        return preg_replace("/[^A-Za-z0-9_\.\-\/]/i" , '' , $string);
    }


    /**
     * Explode request URI to parts
     * @param string $uri
     * @return array
     */
    protected function detectParts(string $uri) : array
    {
        $parts = [];

        $wwwRoot = $this->wwwRoot();
        $rootLen = strlen($wwwRoot);

        if(substr($uri, 0 , $rootLen) === $wwwRoot) {
            $uri = substr($uri, $rootLen);
        }

        $array = explode('/' , $uri);

        for($i = 0, $sz = count($array); $i < $sz; $i++) {
            $parts[] = $array[$i];
        }

        return $parts;
    }


    /**
     * Set configuration options
     * @param ConfigInterface $config
     */
    public function setConfig(ConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * Set configuration option value
     * @param $name
     * @param $value
     */
    public function setConfigOption(string $name , $value)
    {
        $this->config->set($name, $value);
    }

    /**
     * Get request part by index
     * The query string is divided into parts by the delimiter defined by the
     * method Request::setDelimiter are indexed with  0
     * @param int $index — index of the part
     * @return null|string
     */
    public function getPart(int $index) : ?string
    {
        if(isset($this->parts[$index]) && strlen($this->parts[$index])) {
            return $this->parts[$index];
        } else {
            return null;
        }
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
    public function get(string $name , string $type , $default)
    {
        if(isset($this->updatedGet[$name])) {
            return Filter::filterValue($type , $this->updatedGet[$name]);
        }

        if(!isset($_GET[$name])) {
            return $default;
        } else {
            return Filter::filterValue($type , $_GET[$name]);
        }
    }

    /**
     * Get all parameters passed by the $_POST method in an array
     * @return array
     */
    public function postArray() : array
    {
        return array_merge($_POST , $this->updatedPost);
    }

    /**
     * Get all parameters passed by the $_GET method in an array
     * @return array
     */
    public function getArray() : array
    {
        return array_merge($_GET , $this->updatedGet);
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
    public function post(string $name, string $type, $default)
    {
        if (isset($this->updatedPost[$name])) {
            return Filter::filterValue($type, $this->updatedPost[$name]);
        }

        if (!isset($_POST[$name])) {
            return $default;
        } else {
            return Filter::filterValue($type, $_POST[$name]);
        }
    }

    /**
     * Validate the parameter passed by $_POST method
     * @param string $name — parameter name
     * @param string $type —   the value type defining the way the data will be filtered.
     * The ‘Filter’ chapter expands on the list of supported types. Here is the basic list:
     * integer , boolean , float , string, cleaned_string , array и др.
     * @return boolean
     */
    public function validatePost(string $name, string $type)
    {
        if (isset($this->updatedPost[$name])) {
            return ($this->updatedPost[$name] === Filter::filterValue($type, $this->updatedPost[$name]));
        }
        if (!isset($_POST[$name])) {
            return false;
        } else {
            return ($_POST[$name] === Filter::filterValue($type, $_POST[$name]));
        }
    }

    /**
     * Build system request URL
     * The method creates a string based on the defined parameter delimiter and
     * the parameter values array
     * @param array $parts — request parameters array
     * @return string
     */
    public function url(array $parts) : string
    {
        return strtolower($this->wwwRoot() . implode( '/' , $parts));
    }

    /**
     * Process ExtJs Filters
     * @param string $container
     * @param string $method
     * @return array
     */
    public function extFilters($container = 'storefilter' , $method = 'POST')
    {
        if($method == 'POST'){
            $data = self::post($container, 'raw', []);
        }else{
            $data = self::get($container, 'raw', []);
        }

        if(is_string($data))
            $data = json_decode($data , true);

        if(empty($data))
            return [];

        $filter = new Filter\ExtJs();

        return $filter->toDbSelect($data);
    }

    /**
     * Check if request is sent by XMLHttpRequest
     * @return bool
     */
    public function isAjax() : bool
    {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check if any POST requests have been sent
     * @return bool
     */
    public function hasPost() : bool
    {
        if (empty($_POST) && empty($this->updatedPost)) {
            return false;
        }
        return true;
    }


    /**
     * Get cleaned request URL
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Get the list of sent files
     * @return array
     */
    public function files() : array
    {
        if(!isset($_FILES) || empty($_FILES)) {
            return [];
        }

        $result = [];

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
                foreach($data['name'] as $subKey => $subVal){
                    $result[$key][$subKey] = [
                        'name' => $data['name'][$subKey] ,
                        'type' => $data['type'][$subKey] ,
                        'tmp_name' => $data['tmp_name'][$subKey] ,
                        'error' => $data['error'][$subKey] ,
                        'size' => $data['size'][$subKey]
                    ];
                }

            }
        }
        return $result;
    }

    /**
     * Check HTTP_SCHEME for https
     * @return bool
     */
    public function isHttps() : bool
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

    /**
     * Get web toot path
     * @return string
     */
    public function wwwRoot() : string
    {
        $wwwRoot =  '/';

        if($this->config instanceof ConfigInterface && $this->config->offsetExists('wwwRoot')){
            $wwwRoot = $this->config->get('wwwRoot');
        }

        return $wwwRoot;
    }

    /**
     * Get application base url
     * @return string
     */
    public function baseUrl()
    {
        $protocol = 'http://';
        if (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') {
            $protocol = 'https://';
        }

        return $protocol . $_SERVER['HTTP_HOST'] . $this->wwwRoot();
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
    public function server($name, $type, $default)
    {
        if (!isset($_SERVER[$name])) {
            return $default;
        }

        return Filter::filterValue($type, $_SERVER[$name]);
    }

    /**
     * Redefine $_POST parameter
     * @param string $name — parameter name
     * @param mixed $value — parameter value
     */
    public function updatePost(string $name , $value) : void
    {
        $this->updatedPost[$name] = $value;
    }

    /**
     * Set POST data
     * @param array $data
     */
    public function setPostParams(array $data) : void
    {
        $this->updatedPost = $data;
    }

    /**
     * Redefine $_GET parameter
     * @param string $name — parameter name
     * @param mixed $value — parameter value
     */
    public function updateGet($name , $value) : void
    {
        $this->updatedGet[$name] = $value;
    }

    /**
     * Get request parts
     * The query string is divided into parts by the delimiter "/" and indexed from 0
     * @param int $offset, optional default 0 - index to start from
     * @return array
     */
    public function getPathParts(int $offset = 0) : array
    {
        return array_slice($this->parts, $offset);
    }

    /**
     * Set request URI
     * @param string $uri
     */
    public function setUri($uri)
    {
        $this->uri = $this->parseUri($uri);
        $this->parts = $this->detectParts($this->uri);
    }
}