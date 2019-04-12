<?php
/*
 * DVelum project https://github.com/dvelum/dvelum , https://github.com/k-samuel/dvelum , http://dvelum.net
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
declare(strict_types=1);

namespace Dvelum\Orm\Record\Config;

use Dvelum\Orm;

/**
 * Class Field
 * @package Dvelum\Orm\Record\Config
 */
class Field implements \ArrayAccess
{
    protected $config;
    protected $validationError ='';

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Get field config
     * @return array
     */
    public function getConfig() : array
    {
        return $this->config;
    }
    /**
     * Get field type
     * @return string
     */
    public function getType() : string
    {
        if(!isset($this->config['type']))
            return '';

        return (string) $this->config['type'];
    }

    /**
     * Check whether the field is a boolean field
     * @return boolean
     */
    public function isBoolean() : bool
    {
        return (isset($this->config['db_type']) &&  $this->config['db_type'] === 'boolean');
    }

    /**
     * Check whether the field is a numeric field
     * @return boolean
     */
    public function isNumeric() : bool
    {
        return (isset($this->config['db_type']) && in_array($this->config['db_type'] , Orm\Record\Builder::$numTypes , true));
    }

    /**
     * Check whether the field is a integer field
     * @return boolean
     */
    public function isInteger() : bool
    {
        return (isset($this->config['db_type']) && in_array($this->config['db_type'] , Orm\Record\Builder::$intTypes , true));
    }

    /**
     * Check whether the field is a float field
     * @return boolean
     */
    public function isFloat() : bool
    {
        return (isset($this->config['db_type']) && in_array($this->config['db_type'] , Orm\Record\Builder::$floatTypes , true));
    }

    /**
     * Check whether the field is a text field
     * @param mixed $charTypes optional
     * @return boolean
     */
    public function isText($charTypes = false) : bool
    {
        if(!isset($this->config['db_type']))
            return false;

        $isText =  (in_array($this->config['db_type'] , Orm\Record\Builder::$textTypes , true));

        if($charTypes && !$isText)
            $isText =  (in_array($this->config['db_type'] , Orm\Record\Builder::$charTypes, true));

        return $isText;
    }

    /**
     * Check whether the field is a date field
     */
    public function isDateField() : bool
    {
        return (isset($this->config['db_type']) && in_array($this->config['db_type'] , Orm\Record\Builder::$dateTypes, true));
    }

    /**
     * Check if the field value is required
     * @return boolean
     */
    public function isRequired() : bool
    {
        if(isset($this->config['required']) &&  $this->config['required'])
            return true;
        else
            return false;
    }

    /**
     * Check if field can be used for search
     * @return boolean
     */
    public function isSearch() : bool
    {
        if(isset($this->config['is_search']) && $this->config['is_search'])
            return true;
        else
            return false;
    }

    /**
     * Check if field is encrypted
     * @return boolean
     */
    public function isEncrypted() : bool
    {
        if(isset($this->config['type']) && $this->config['type']==='encrypted')
            return true;
        else
            return false;
    }

    /**
     * Check if the field is a link
     * @return boolean
     */
    public function isLink() : bool
    {
        if(isset($this->config['type']) && $this->config['type']==='link')
            return true;
        else
            return false;
    }

    /**
     * Check if the field is a link to the dictionary
     * @return boolean
     */
    public function isDictionaryLink() : bool
    {
        if(isset($this->config['type']) && $this->config['type']==='link' && isset($this->config['link_config']) && is_array($this->config['link_config']) && $this->config['link_config']['link_type']==='dictionary')
            return true;
        else
            return false;
    }

    /**
     * Check if html is allowed\
     * @return boolean
     */
    public function isHtml() : bool
    {
        if(isset($this->config['allow_html']) && $this->config['allow_html'])
            return true;

        return false;
    }

    /**
     * Get the database type for the field
     * @return string
     */
    public function getDbType() : string
    {
        return $this->config['db_type'];
    }

    /**
     * Check whether the field should be unique
     * @return boolean
     */
    public function isUnique() : bool
    {
        if(!isset($this->config['unique']))
            return false;

        if(is_string($this->config['unique']) && strlen($this->config['unique']))
            return true;

        return (boolean) $this->config['unique'];
    }

    /**
     * Check if a field is a object link
     * @return boolean
     */
    public function isObjectLink() : bool
    {
        if(isset($this->config['type']) && $this->config['type']==='link' && isset($this->config['link_config']) && is_array($this->config['link_config']) && $this->config['link_config']['link_type']===Orm\Record\Config::LINK_OBJECT)
            return true;
        else
            return false;
    }

    /**
     * Check if a field is a MultiLink (a list of links to objects of the same type)
     * @return bool
     */
    public function isMultiLink() : bool
    {
        if(isset($this->config['type']) && $this->config['type']==='link' && isset($this->config['link_config']) && is_array($this->config['link_config']) && $this->config['link_config']['link_type']===Orm\Record\Config::LINK_OBJECT_LIST)
            return true;
        else
            return false;
    }

    /**
     * Check if field is ManyToMany relation
     * @return boolean
     */
    public function isManyToManyLink() : bool
    {
        if(isset($this->config['type']) && $this->config['type']==='link'
            && is_array($this->config['link_config'])
            && $this->config['link_config']['link_type'] === Orm\Record\Config::LINK_OBJECT_LIST
            && isset($this->config['link_config']['relations_type'])
            && $this->config['link_config']['relations_type'] === Orm\Record\Config::RELATION_MANY_TO_MANY
        ){
            return true;
        }
        return false;
    }

    /**
     * Get the name of the object referenced by the field
     * @return string | false on error
     */
    public function getLinkedObject()
    {
        if(!$this->isLink())
            return false;

        return 	$this->config['link_config']['object'];
    }

    /**
     * Get field default value. Note! Method return false if value not specified
     * @return string | false
     */
    public function getDefault()
    {
        if(isset($this->config['db_default']))
            return $this->config['db_default'];
        else
            return false;
    }

    /**
     * Check if field has default value
     * @return boolean
     */
    public function hasDefault() : bool
    {
        if(isset($this->config['db_default']) && $this->config['db_default']!==false)
            return true;
        else
            return false;
    }

    /**
     * Check if field is numeric and unsigned
     * @return boolean
     */
    public function isUnsigned() : bool
    {
        if(!$this->isNumeric())
            return false;

        if(isset($this->config['db_unsigned']) && $this->config['db_unsigned'])
            return true;
        else
            return false;
    }

    /**
     * Check if field can be null
     * @return boolean
     */
    public function isNull() : bool
    {
        if(isset($this->config['db_isNull']) && $this->config['db_isNull'])
            return true;
        else
            return false;
    }

    /**
     * Get the name of the dictionary that is referenced by the field
     * @return string | bool on error
     */
    public function getLinkedDictionary()
    {
        if(!$this->isDictionaryLink())
            return false;

        return 	$this->config['link_config']['object'];
    }

    /**
     * Check if field is virtual (no database representation)
     * @return bool
     */
    public function isVirtual() : bool
    {
        return $this->isMultiLink();
    }


    //==== Start of ArrayAccess implementation ===
    public function offsetSet($offset, $value)
    {
        $this->config[$offset] = $value;
    }
    public function offsetExists($offset)
    {
        return isset($this->config[$offset]);
    }
    public function offsetUnset($offset)
    {
        unset($this->config[$offset]);
    }
    public function offsetGet($offset)
    {
        return isset($this->config[$offset]) ? $this->config[$offset] : null;
    }
    //====End of ArrayAccess implementation ====

    public function __toArray() : array
    {
        return $this->config;
    }

    public function __isset($name)
    {
        return isset($this->config[$name]);
    }

    /**
     * Apply value filter
     * @param mixed $value
     * @return mixed
     */
    public function filter($value)
    {
        return $value;
    }

    /**
     * Validate value
     * @param mixed $value
     * @return bool
     */
    public function validate($value) : bool
    {
        if($this->isRequired() && !strlen((string)$value)){
            $this->validationError = 'Field '. $this->getName().' cannot be empty';
            return false;
        }
        return true;
    }

    /**
     * Get field name
     * @return string
     */
    public function getName() : string
    {
        return $this->config['name'];
    }

    /**
     * Get last validation error
     * @return string
     */
    public function getValidationError() : string
    {
        return $this->validationError;
    }

    /**
     * Get field title
     * @return string
     */
    public function getTitle() : string
    {
        return $this->config['title'];
    }

    /**
     * Check if fieldIs System
     * @return bool
     */
    public function isSystem() : bool
    {
        if(isset($this->config['system']) && $this->config['system']){
            return true;
        }
        return false;
    }
}