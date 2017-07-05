<?php
/*
 * DVelum project http://code.google.com/p/dvelum/ , http://dvelum.net
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

namespace Dvelum;

use Dvelum\Config\Storage\StorageInterface;

class Lang
{
    protected $defaultDictionary = '';

    protected $storage = false;

    protected $dictionaries = [];
    protected $loaders = [];

    /**
     * Set default localization
     * @param string $name
     * @throws \Exception
     */
    public function setDefaultDictionary(string $name): void
    {
        if (!isset($this->dictionaries[$name]) && !isset($this->loaders[$name])) {
            throw new \Exception('Dictionary ' . $name . ' is not found');
        }

        $this->defaultDictionary = $name;
    }

    /**
     * Get default dictionary (lang)
     * @return string
     */
    public function getDefaultDictionary(): string
    {
        return $this->defaultDictionary;
    }

    /**
     * Add localization dictionary
     * @param string $name — localization name
     * @param Lang\Dictionary $dictionary — configuration object
     * @return void
     */
    public function addDictionary(string $name, Lang\Dictionary $dictionary): void
    {
        $this->dictionaries[$name] = $dictionary;
    }

    /**
     * Add localization loader
     * Backward compatibility
     * @param string $name - dictionary name
     * @param mixed $src - dictionary source
     * @param int $type - Config constant
     * @deprecated
     */
    static public function addDictionaryLoader(string $name, $src, int $type = Config\Factory::File_Array): void
    {
        /**
         * @var Lang $langService
         */
        $langService = Service::get('lang');
        $langService->addLoader($name, $src, $type);
    }

    /**
     * Add localization loader
     * @param string $name - dictionary name
     * @param mixed $src - dictionary source
     * @param int $type - Config constant
     */
    public function addLoader(string $name, $src, int $type = Config\Factory::File_Array): void
    {
        $this->loaders[$name] = array('src' => $src, 'type' => $type);
    }

    /**
     * Get localization dictionary by localization name or get default dictionary
     * @param string $name optional,
     * @throws \Exception
     * @return Lang\Dictionary
     */
    public function getDictionary(?string $name = null): Lang\Dictionary
    {
        if (empty($name)) {
            $name = $this->defaultDictionary;
        }

        if (isset($this->dictionaries[$name])) {
            return $this->dictionaries[$name];
        }

        if (!isset($this->dictionaries[$name]) && !isset($this->loaders[$name])) {
            throw new \Exception('Lang::lang Dictionary "' . $name . '" is not found');
        }

        $this->dictionaries[$name] = new Lang\Dictionary($name, $this->loaders[$name]);

        return $this->dictionaries[$name];
    }

    /**
     * Get link to localization dictionary by localization name or
     * get default dictionary
     * @param string $name optional,
     * @throws \Exception
     * @return Lang\Dictionary
     */
    static public function lang(?string $name = null): Lang\Dictionary
    {
        /**
         * @var Lang $langService
         */
        $langService = Service::get('lang');
        return $langService->getDictionary($name);
    }

    /**
     * Get configuration storage
     * @return StorageInterface
     */
    public function getStorage(): StorageInterface
    {
        if (!$this->storage) {
            $this->storage = new Config\Storage\File\AsArray();
        }
        return $this->storage;
    }

    /**
     * Get configuration storage
     * @return StorageInterface
     */
    static public function storage(): StorageInterface
    {
        /**
         * @var Lang $langService
         */
        $langService = Service::get('lang');
        return $langService->getStorage();
    }
}