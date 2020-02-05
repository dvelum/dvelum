<?php
/**
 *  DVelum project https://github.com/dvelum/dvelum
 *  Copyright (C) 2011-2019  Kirill Yegorov
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
 */

declare(strict_types=1);

namespace Dvelum\Page;

class Page
{
    static protected $pages = [];

    /**
     * @var string
     */
    protected $templatesPath = '';
    /**
     * @var string
     */
    protected $title = '';
    /**
     * @var string
     */
    protected $htmlTitle = '';
    /**
     * @var string
     */
    protected $code = 'index';
    /**
     * @var int
     */
    protected $id = 0;
    /**
     * @var string
     */
    protected $text = '';
    /**
     * @var string
     */
    protected $metaKeywords = '';
    /**
     * @var string
     */
    protected $metaDescription = '';
    /**
     * @var string|null
     */
    protected $robots = null;
    /**
     * @var string|null
     */
    protected $canonical = null;
    /**
     * @var string
     */
    protected $theme = 'default';
    /**
     * @var OpenGraph|null
     */
    protected $openGraph = null;
    /**
     * @var string|null
     */
    protected $csrfToken = null;

    /**
     * @var array
     */
    protected $properties = [];

    static public function factory(string $id = 'default'):Page
    {
        if(!isset(static::$pages[$id])){
            static::$pages[$id] = new static();
        }
        return static::$pages[$id];
    }

    protected function __construct(){}

    /**
     * @param array $properties
     */
    public function setProperties(array $properties) : void
    {
        foreach ($properties as $k=>$v){
            $this->properties[$k] = $v;
        }
    }

    /**
     * @param string $name
     * @return mixed
     * @throws \Exception
     */
    public function getProperty(string $name)
    {
        if(!isset($this->properties[$name])){
            throw new \Exception('Undefined page property '.$name);
        }
        return $this->properties[$name];
    }

    /**
     * @return OpenGraph
     */
    public function openGraph():OpenGraph
    {
        if(empty($this->openGraph)){
            $this->openGraph = new OpenGraph();
        }
        return $this->openGraph;
    }

    /**
     * @return string
     */
    public function getTemplatesPath(): string
    {
        return $this->templatesPath;
    }

    /**
     * @param string $templatesPath
     */
    public function setTemplatesPath(string $templatesPath): void
    {
        $this->templatesPath = $templatesPath;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getHtmlTitle(): string
    {
        return $this->htmlTitle;
    }

    /**
     * @param string $htmlTitle
     */
    public function setHtmlTitle(string $htmlTitle): void
    {
        $this->htmlTitle = $htmlTitle;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @param string $text
     */
    public function setText(string $text): void
    {
        $this->text = $text;
    }

    /**
     * @param string $text
     */
    public function addText(string $text):void
    {
        $this->text.= $text;
    }

    /**
     * @return string
     */
    public function getMetaKeywords(): string
    {
        return $this->metaKeywords;
    }

    /**
     * @param string $metaKeywords
     */
    public function setMetaKeywords(string $metaKeywords): void
    {
        $this->metaKeywords = $metaKeywords;
    }

    /**
     * @return string
     */
    public function getMetaDescription(): string
    {
        return $this->metaDescription;
    }

    /**
     * @param string $metaDescription
     */
    public function setMetaDescription(string $metaDescription): void
    {
        $this->metaDescription = $metaDescription;
    }

    /**
     * @return string|null
     */
    public function getRobots(): ?string
    {
        return $this->robots;
    }

    /**
     * @param string|null $robots
     */
    public function setRobots(?string $robots): void
    {
        $this->robots = $robots;
    }

    /**
     * @return string|null
     */
    public function getCanonical(): ?string
    {
        return $this->canonical;
    }

    /**
     * @param string|null $canonical
     */
    public function setCanonical(?string $canonical): void
    {
        $this->canonical = $canonical;
    }

    /**
     * @return string
     */
    public function getTheme(): string
    {
        return $this->theme;
    }

    /**
     * @param string $theme
     */
    public function setTheme(string $theme): void
    {
        $this->theme = $theme;
    }

    /**
     * @return string|null
     */
    public function getCsrfToken(): ?string
    {
        return $this->csrfToken;
    }

    /**
     * @param string|null $csrfToken
     */
    public function setCsrfToken(?string $csrfToken): void
    {
        $this->csrfToken = $csrfToken;
    }

    /**
     * Get path to the folder with current theme templates
     * @return string
     */
    public function getThemePath()
    {
        return $this->templatesPath . $this->theme . '/';
    }
}