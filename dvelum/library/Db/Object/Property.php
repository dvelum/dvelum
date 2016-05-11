<?php
/**
 * Db_Object Property class
 * Note: "id"  property creates automatically
 * @package Db
 * @subpackage Db_Object
 * @author Kirill A Egorov kirill.a.egorov@gmail.com 
 * @copyright Copyright (C) 2011-2012  Kirill A Egorov, 
 * DVelum project http://code.google.com/p/dvelum/ , http://dvelum.net
 * @license General Public License version 3
 */
class Db_Object_Property
{
    
    /**
     *  List of acceptable properties
     * @var array
     */
    public static $_acceptedData = array(
            'title' , 
            'required' , 
            'allow_html' , 
            'db_type' , 
            'db_len' , 
            'db_isNull' , 
            'db_default' , 
            'db_unsigned' , 
            'db_scale' , 
            'db_precision' , 
            'db_auto_increment' , 
            'type' , 
            'link_config' , 
            'is_search' , 
            'unique' , 
            'validator' , 
            'system' , 
            'lazyLang',
            'locked',
            'readonly',
            'connection',
            'use_db_prefix',
            'hidden',
            'relations_type'
    );
    public static $numberLength = array(
            'tinyint' => 3 , 
            'smallint' => 5 , 
            'mediumint' => 8 , 
            'int' => 10 , 
            'bigint' => 20
    );
    
    /**
     * Properties data
     * @var array
     */
    protected $_data = array();
    protected $_name = array();

    public function __construct($name)
    {
        $this->_name = $name;
    }

    /**
     * Set property data
     * @param array $data
     * @throws Exception
     * @return void
     */
    public function setData(array $data)
    {
        foreach($data as $key => $value)
        {
            if(in_array($key , self::$_acceptedData))
                $this->_data[$key] = $value;
            else
                throw new Exception('Invalid property name "' . $key . '"');
        }
    }

    /**
     * Empty data
     * @return void
     */
    public function clear()
    {
        $this->_data = array();
    }

    /**
     * Getter
     * @param string $key
     * @throws Exception
     * @return mixed
     */
    public function __get($key)
    {
        if(isset($this->_data[$key]))
            return $this->_data[$key];
        else
            throw new Exception('Invalid property name "' . $key . '"');
    }

    public function __isset($key)
    {
        return isset($this->_data[$key]);
    }

    /**
     * Get SQL part
     * @return string
     */
    public function __toSql()
    {
        $s = '';
        $isNumber = false;

        switch(strtolower($this->db_type))
        {
            case 'boolean' :
                $this->_data['db_isNull'] = false;
                $s .= '`' . $this->_name . '` ' . $this->_data['db_type'] . ' ';
                break;
            case 'tinyint' :
            case 'smallint' :
            case 'mediumint' :
            case 'int' :
            case 'bigint' :
                $this->_data['db_len'] = self::$numberLength[$this->db_type];

                $s .= '`' . $this->_name . '` ' . $this->_data['db_type'] . ' (' . $this->_data['db_len'] . ')';

                if(isset($this->_data['db_unsigned']) && $this->_data['db_unsigned'] && strtolower($this->db_type) != 'boolean')
                    $s .= ' UNSIGNED ';

                if($this->db_type !== 'boolean' && isset($this->_data['db_auto_increment']) && $this->_data['db_auto_increment'])
                    $s .= ' AUTO_INCREMENT ';

                $isNumber = true;
                break;

            case 'bit' :
                $s .= '`' . $this->_name . '` ' . $this->_data['db_type'] . ' ';

                if(isset($this->_data['db_unsigned']) && $this->_data['db_unsigned'] && strtolower($this->db_type) != 'boolean')
                    $s .= ' UNSIGNED ';

                $isNumber = true;
                break;
            case 'real' :
            case 'float' :
            case 'double' :
            case 'decimal' :

                $s .= '`' . $this->_name . '` ' . $this->_data['db_type'] . '(' . $this->_data['db_scale'] . ',' . $this->_data['db_precision'] . ') ';

                if(isset($this->_data['db_unsigned']) && $this->_data['db_unsigned'])
                    $s .= ' UNSIGNED ';

                $isNumber = true;
                break;
                http: //dvelum.demo/adminarea/news.html


                break;

            case 'char' :
            case 'varchar' :

                /*

                Auto set default '' for NOT NULL string properties
                if(!isset($this->_data['db_isNull']) || !$this->_data['db_isNull'])
                    if(!isset($this->_data['db_default']) || $this->_data['db_default'] === false)
                        $this->_data['db_default'] = '';
                */

                $s = '`' . $this->_name . '` ' . $this->_data['db_type'] . ' (' . $this->_data['db_len'] . ')  ';

                break;
            case 'date' :
            case 'time' :
            case 'timestamp' :
            case 'datetime' :
                if(isset($this->_data['db_default']) && !strlen($this->_data['db_default']))
                    unset($this->_data['db_default']);
                $s = '`' . $this->_name . '` ' . $this->_data['db_type'] . ' ';
                break;
            case 'tinytext' :
            case 'text' :
            case 'mediumtext' :
            case 'longtext' :

                $s = '`' . $this->_name . '` ' . $this->_data['db_type'] . ' ';
                if(isset($this->_data['db_default']))
                    unset($this->_data['db_default']);
                if(!isset($this->_data['required']) || !$this->_data['required'])
                    $this->_data['db_isNull'] = true;

                break;
        }

        if(!$this->_data['db_isNull'])
        {
            $s .= 'NOT NULL ';

            if(isset($this->_data['db_default']) && $this->_data['db_default'] !== false)
            {
                if($isNumber)
                    $s .= " DEFAULT " . $this->_data['db_default'] . " ";
                else
                    $s .= " DEFAULT '" . $this->_data['db_default'] . "' ";
            }
        }
        else
        {
            $s .= ' NULL ';
        }

        $s .= " COMMENT '" . addslashes($this->_data['title']) . "' ";
        return $s;
    }

    /**
     * Setter
     * @param string $key
     * @throws Exception
     * @param mixed $value
     */
    public function __set($key , $value)
    {
        if(in_array(self::$_acceptedData , $key))
            $this->_data[$key] = $value;
        else
            throw new Exception('Invalid property name');
    }

    /**
       * Property filter
       * @param array $fieldInfo - property config data
       * @param mixed $value
       * @throws Exception
       * @return mixed
       */
    static public function filter($fieldInfo , $value)
    {
        switch(strtolower($fieldInfo['db_type']))
        {
            case 'tinyint' :
            case 'smallint' :
            case 'mediumint' :
            case 'int' :
            case 'bigint' :
                $value = Filter::filterValue('int' , $value);
                break;
            
            case 'float' :
            case 'double' :
            case 'decimal' :
                $value = Filter::filterValue('float' , $value);
                
                break;
            case 'bool' :
            case 'boolean' :
                $value = Filter::filterValue('boolean' , $value);
                break;
            case 'date' :
            case 'time' :
            case 'timestamp' :
            case 'datetime' :
                $value = Filter::filterValue('string' , $value);
                break;
            case 'tinytext' :
            case 'text' :
            case 'bit' :
            case 'char' :
            case 'varchar' :
            case 'mediumtext' :
            case 'longtext' :
            case 'char' :
            case 'varchar' :
                if(!isset($fieldInfo['allow_html']) || !$fieldInfo['allow_html'])
                    $value = Filter::filterValue('string' , $value);
                break;
                //  case 'bit':
                //		$value = preg_replace ('/[^01]*/', '', $value);
                break;
            default :
                throw new Exception('Invalid property type "' . $fieldInfo['db_type'] . '"');
        }
        return $value;
    }
}
