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
 * The class is used for collecting the properties of
 * the page being displayed. The “Singleton” pattern
 * being implemented, it may be called just once from
 * anywhere within the application.
 *
 */
class Page
{
    protected static $_instance;
    /**
     * @var Externals_Expert
     */
    protected $_externalsExpert = false;
    public $title = '';
    public $html_title = '';
    public $code = 'index';
    public $id = 0;
    public $meta_description = '';
    public $meta_keywords = '';
    public $theme = 'default';
    protected $_ogData = array(
        'image' => '' ,
        'title' => '' ,
        'description' => '' ,
        'url' => ''
    );

    protected function __construct(){}

    protected function __clone(){}

    /**
     * Get Object Instance (Singleton)
     * @return Page
     */
    static public function getInstance()
    {
        if(!isset(self::$_instance))
            self::$_instance = new self();

        return self::$_instance;
    }

    /**
     * Define Open Graph property
     * @param string $key
     * @param string $value
     */
    public function setOgProperty($key , $value)
    {
        $this->_ogData[$key] = $value;
    }

    /**
     * Define Externals expert adapter
     * @param Externals_Expert $expert
     */
    public function setExternalsExpert(Externals_Expert $expert)
    {
        $this->_externalsExpert = $expert;
    }

    /**
     * Generate meta tags with Open Graph metadata
     * @return string
     */
    public function getOgMeta()
    {
        $s = '';
        foreach($this->_ogData as $key => $value)
            if(strlen($value))
                $s .= '<meta property="og:' . $key . '" content="' . $value . '"/>';

        return $s;
    }

    /**
     * Set templates directory
     * @param string $path
     */
    public function setTemplatesPath($path)
    {
        $this->_templatesPath = $path;
    }

    /**
     * Get templates directory
     * @return string
     */
    public function getTemplatesPath()
    {
        return $this->_templatesPath;
    }

    /**
     * Get path to the folder with current theme templates
     * @return string
     */
    public function getThemePath()
    {
        return $this->_templatesPath . $this->theme . '/';
    }
}