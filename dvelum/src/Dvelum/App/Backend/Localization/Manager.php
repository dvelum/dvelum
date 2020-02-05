<?php
/**
 *  DVelum project https://github.com/dvelum/dvelum
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
 */
declare(strict_types=1);

namespace Dvelum\App\Backend\Localization;

use Dvelum\Config\ConfigInterface;
use Dvelum\Config\Factory;
use Dvelum\Service;
use Dvelum\Lang;
use \Exception;

/**
 * Class Manager
 * @package Dvelum\App\Backend\Localization
 */
class Manager
{
    /**
     * @var ConfigInterface
     */
    protected $appConfig;
    /**
     * Message language
     * @var Lang\Dictionary
     */
    protected $lang;

    protected $indexLanguage = 'en';

    /**
     * Localizations file path
     * @var array
     */
    protected $langsPaths;

    /**
     * @param ConfigInterface $appConfig
     */
    public function __construct(ConfigInterface $appConfig)
    {
        $this->appConfig = $appConfig;
        $this->langsPaths = Lang::storage()->getPaths();
        $this->lang = Lang::lang();
    }

    /**
     * Get list of system languages
     * @param boolean $onlyMain - optional. Get only global locales without subpackages
     * @return array
     */
    public function getLangs($onlyMain = true)
    {
        $langStorage = Lang::storage();
        $langs = $langStorage->getList(false, !$onlyMain);
        $paths = $langStorage->getPaths();

        $data = [];
        foreach ($langs as $file) {
            $file = str_replace($paths, '', $file);
            if (strpos($file, 'index') === false && basename($file) !== 'objects.php' && strpos($file,
                    '/objects/') == false) {
                $data[] = substr($file, 0, -4);
            }
        }
        return array_unique($data);
    }

    /**
     * Rebuild all localization indexes
     */
    public function rebuildAllIndexes()
    {
        $this->rebuildIndex(false);
        $sub = $this->getSubPackages();
        foreach ($sub as $pack) {
            $this->rebuildIndex($pack);
        }
    }

    /**
     * Get language subpackages
     * @param string|bool $lang - optional
     * @return array
     */
    public function getSubPackages($lang = false)
    {
        $data = [];

        if (!$lang) {
            $lang = $this->indexLanguage;
        }

        if ($lang) {
            $lang = $lang . '/';
        } else {
            $lang = false;
        }

        $langs = Lang::storage()->getList($lang, false);

        foreach ($langs as $file) {
            if (basename($file) !== 'objects.php') {
                $data[] = substr(basename($file), 0, -4);
            }
        }
        return $data;
    }

    /**
     * Get list of sub dictionaries (names only)
     * @return array
     */
    public function getSubDictionaries()
    {
        /**
         * @var Lang $langService
         */
        $langService = Service::get('lang');
        $language = $langService->getDefaultDictionary();

        $result = $this->getSubPackages($language);

        if (!empty($result)) {
            foreach ($result as &$v) {
                $v = str_replace(array('/', '\\'), '', $v);
            }
        } else {
            $result = [];
        }
        return $result;
    }

    /**
     * Rebuild language index
     * @param string | bool $subPackage - optional
     * @throws Exception
     */
    public function rebuildIndex($subPackage = false)
    {
        $indexFile = '';

        if (empty($subPackage)) {
            $indexName = $this->getIndexName();
            $indexBaseName = $this->indexLanguage . '.php';
        } else {
            $indexName = $this->getIndexName((string)$subPackage);
            $indexBaseName = $this->indexLanguage . '/' . $subPackage . '.php';
        }

        try{
            $indexBase = Lang::storage()->get($indexBaseName);
        }catch (\Throwable $e){
            throw new Exception($this->lang->get('CANT_LOAD') . ' ' . $indexBaseName);
        }

        $baseKeys = array_keys($indexBase->__toArray());

        $indexPath = Lang::storage()->getPath($indexName);
        $writePath = Lang::storage()->getWrite();
        if (!file_exists((string)$indexPath) && !file_exists($writePath . $indexName)) {
            if (!\Dvelum\Utils::exportArray($writePath . $indexFile, [])) {
                throw new Exception($this->lang->get('CANT_WRITE_FS') . ' ' . $writePath . $indexName);
            }
        }
        $storage = Lang::storage();
        try{
            $indexConfig = $storage->get($indexName);
        }catch (\Throwable $e){
            throw new Exception($this->lang->get('CANT_LOAD') . ' ' . $indexName);
        }

        $indexConfig->removeAll();
        $indexConfig->setData($baseKeys);
        if (!$storage->save($indexConfig)) {
            throw new Exception($this->lang->get('CANT_WRITE_FS') . ' ' . $indexConfig->getName());
        }
    }

    /**
     * Get dictionary index name
     * @param string $dictionary
     * @return string
     */
    public function getIndexName($dictionary = '')
    {
        return str_replace('/', '_', $dictionary) . '_index.php';
    }

    /**
     * Get dictionary_index
     * @param string $dictionary
     * @return boolean|array
     */
    public function getIndex($dictionary = '')
    {
        $subPackage = basename($dictionary);
        $indexName = $this->getIndexName($subPackage);

        $indexFile = Lang::storage()->getPath($indexName);

        if (!file_exists((string)$indexFile)) {
            return false;
        }

        $data = include $indexFile;

        if (!is_array($data)) {
            return false;
        }

        return $data;
    }

    /**
     * Update index content
     * @param array $data
     * @param string $dictionary - optional
     * @throws Exception
     */
    public function updateIndex($data, $dictionary)
    {
        $subPackage = basename($dictionary);
        $indexName = $this->getIndexName($subPackage);

        $writePath = Lang::storage()->getWrite();

        if (!file_exists($writePath . $indexName)) {
            if (!\Dvelum\Utils::exportArray($writePath . $indexName, [])) {
                throw new Exception($this->lang->get('CANT_WRITE_FS') . ' ' . $writePath . $indexName);
            }
        }
        $storage = Lang::storage();
        $indexConfig = $storage->get($indexName);
        $indexConfig->removeAll();
        $indexConfig->setData($data);
        if (!$storage->save($indexConfig)) {
            throw new Exception($this->lang->get('CANT_WRITE_FS') . ' ' . $writePath . $indexName);
        }
    }

    /**
     * Get localization config
     * @param string $dictionary
     * @return array
     */
    public function getLocalization($dictionary)
    {
        $dictionaryData = Lang::storage()->get($dictionary . '.php')->__toArray();

        if (strpos($dictionary, '/') !== false) {
            $index = $this->getIndex($dictionary);
        } else {
            $index = $this->getIndex();
        }

        if (!is_array($index)) {
            return [];
        }

        $keys = array_keys($dictionaryData);
        $newKeys = array_diff($keys, $index);
        $result = [];

        foreach ($index as $dKey) {
            $value = '';
            $sync = true;
            if (isset($dictionaryData[$dKey])) {
                $value = $dictionaryData[$dKey];
            } else {
                $sync = false;
            }

            $result[] = array('id' => $dKey, 'key' => $dKey, 'title' => $value, 'sync' => $sync);
        }

        if (!empty($newKeys)) {
            foreach ($newKeys as $key) {
                $result[] = array('id' => $key, 'key' => $key, 'title' => $dictionaryData[$key], 'sync' => true);
            }
        }
        return $result;
    }

    /**
     * Add key to localization index
     * @param string $key
     * @param string $dictionary
     * @throws \Exception
     */
    public function addToIndex($key, $dictionary = '')
    {
        /**
         * @var array $index
         */
        $index = $this->getIndex($dictionary);
        if(empty($index)){
            $index = [];
        }

        if (!in_array($key, $index, true)) {
            $index[] = $key;
        }

        $this->updateIndex($index, $dictionary);
    }

    /**
     * Remove key from localization index
     * @param string $key
     * @param string $dictionary
     * @throws \Exception
     */
    public function removeFromIndex($key, $dictionary = '')
    {
        /**
         * @var array $index
         */
        $index = $this->getIndex($dictionary);
        if(empty($index)){
            $index = [];
        }

        if (!in_array($key, $index, true)) {
            return;
        }
        foreach ($index as $k => $v) {
            if ($v === $key) {
                unset($index[$k]);
            }
        }
        $this->updateIndex($index, $dictionary);
    }

    /**
     * Add dictionary record
     * @param string $dictionary
     * @param string $key
     * @param array $langs
     * @throws Exception
     */
    public function addRecord($dictionary, $key, array $langs)
    {
        $isSub = false;
        $dictionaryName = $dictionary;

        if (strpos($dictionary, '/') !== false) {
            $tmp = explode('/', $dictionary);
            $dictionaryName = $tmp[1];
            $isSub = true;
        }

        if ($isSub) {
            $index = $this->getIndex($dictionary);
        } else {
            $index = $this->getIndex();
        }

        /**
         * @var array $index
         */
        if(empty($index)){
            $index = [];
        }

        // add index for dictionary key
        if (!in_array($key, $index, true)) {
            if ($isSub) {
                $this->addToIndex($key, $dictionary);
            } else {
                $this->addToIndex($key);
            }
        }

        $writePath = Lang::storage()->getWrite();
        $storage = Lang::storage();
        if (!$isSub) {
            foreach ($langs as $langName => $value) {
                $langFile = $writePath . $langName . '.php';
                try{
                    $langConfig = $storage->get($langName . '.php');
                }catch (\Throwable $e){
                    throw new Exception($this->lang->get('CANT_LOAD') . ' ' . $langName);
                }
                $langConfig->set($key, $value);
                if (!$storage->save($langConfig)) {
                    throw new Exception($this->lang->get('CANT_WRITE_FS') . ' ' . $langFile);
                }
            }

        } else {
            foreach ($langs as $langName => $value) {
                $langFile = $writePath . $langName . '/' . $dictionaryName . '.php';
                try{
                    $langConfig = Lang::storage()->get($langName . '/' . $dictionaryName . '.php');
                }catch (\Throwable $e){
                    throw new Exception($this->lang->get('CANT_LOAD') . ' ' . $langName . '/' . $dictionaryName);
                }
                $langConfig->set($key, $value);
                if (!$storage->save($langConfig)) {
                    throw new Exception($this->lang->get('CANT_WRITE_FS') . ' ' . $langFile);
                }
            }
        }
    }

    /**
     * Check if file exists and writable
     * @param string $file
     * @return bool
     */
    protected function checkCanEdit($file) : bool
    {
        if (file_exists($file) && is_writable($file)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Remove key from localizations
     * @param string $dictionary
     * @param string $key
     * @throws Exception
     */
    public function removeRecord($dictionary, $key)
    {
        $isSub = false;

        if (strpos($dictionary, '/') !== false) {
            $tmp = explode('/', $dictionary);
            $dictionaryName = $tmp[1];
            $isSub = true;
        }else{
            $dictionaryName = $dictionary;
        }

        if ($isSub) {
            $this->removeFromIndex($key, $dictionary);
        } else {
            $this->removeFromIndex($key);
        }

        $mainLangs = $this->getLangs(true);

        $writePath = Lang::storage()->getWrite();
        $storage = Lang::storage();
        if (!$isSub) {
            foreach ($mainLangs as $langName) {
                $langFile = $writePath . $langName . '.php';
                try{
                    $langConfig = Lang::storage()->get($langName . '.php');
                }catch (\Throwable $e){
                    throw new Exception($this->lang->get('CANT_LOAD') . ' ' . $langName);
                }

                $langConfig->remove($key);
                if (!$storage->save($langConfig)) {
                    throw new Exception($this->lang->get('CANT_WRITE_FS') . ' ' . $langFile);
                }
            }
        } else {
            foreach ($mainLangs as $langName) {
                $langFile = $writePath . $langName . '/' . $dictionaryName . '.php';
                try{
                    $langConfig = Lang::storage()->get($langName . '/' . $dictionaryName . '.php');
                }catch (\Throwable $e){
                    throw new Exception($this->lang->get('CANT_LOAD') . ' ' . $langName . '/' . $dictionaryName);
                }

                $langConfig->remove($key);
                if (!$storage->save($langConfig)) {
                    throw new Exception($this->lang->get('CANT_WRITE_FS') . ' ' . $langFile);
                }
            }
        }
    }

    /**
     * Update localization records
     * @param string $dictionary
     * @param array $data
     * @throws Exception
     */
    public function updateRecords($dictionary, $data)
    {
        $writePath = Lang::storage()->getWrite() . $dictionary . '.php';

        $langConfig = Lang::storage()->get($dictionary . '.php');

        foreach ($data as $rec) {
            $langConfig->set($rec['id'], $rec['title']);
        }

        $storage = Lang::storage();
        if (!$storage->save($langConfig)) {
            throw new Exception($this->lang->get('CANT_WRITE_FS') . ' ' . $writePath);
        }
    }

    /**
     * Check if dictionary exists (only sub dictionaies not languages)
     * @param string $name
     * @return boolean
     */
    public function dictionaryExists($name)
    {
        $list = $this->getSubDictionaries();

        if (in_array($name, $list, true)) {
            return true;
        }

        return false;
    }

    /**
     * Create sub dicionary
     * @param string $name
     * @throws Exception
     */
    public function createDictionary($name)
    {
        $writePath = Lang::storage()->getWrite();
        $indexPath = $writePath . $this->getIndexName($name);

        $indexLocation = dirname($indexPath);

        if (!file_exists($indexLocation) && !@mkdir($indexLocation, 0775, true)) {
            throw new Exception($this->lang->get('CANT_WRITE_FS') . ' ' . $indexLocation);
        }

        if (!\Dvelum\Utils::exportArray($indexPath, [])) {
            throw new Exception($this->lang->get('CANT_WRITE_FS') . ' ' . $indexPath);
        }

        $langs = $this->getLangs(true);

        foreach ($langs as $lang) {
            $fileLocation = $writePath . $lang;

            if (!file_exists($fileLocation) && !@mkdir($fileLocation, 0775, true)) {
                throw new Exception($this->lang->get('CANT_WRITE_FS') . ' ' . $fileLocation);
            }

            $filePath = $fileLocation . '/' . $name . '.php';

            if (!\Dvelum\Utils::exportArray($filePath, [])) {
                throw new Exception($this->lang->get('CANT_WRITE_FS') . ' ' . $filePath);
            }
        }
    }

    /**
     * Create JS lang files
     * @throw Exception
     */
    public function compileLangFiles()
    {
        $jsPath = $this->appConfig->get('js_lang_path');;
        $langs = $this->getLangs(false);

        /**
         * @var Lang $langService
         */
        $langService = Service::get('lang');

        $exceptDirs = ['objects', 'modules'];

        foreach ($langs as $lang) {
            $name = $lang;

            $langService->addLoader($name, $lang . '.php', Factory::File_Array);

            $filePath = $jsPath . $lang . '.js';

            $dir = dirname($lang);

            if (in_array(basename($dir), $exceptDirs, true)) {
                continue;
            }

            if (!empty($dir) && $dir !== '.' && !is_dir($jsPath . '/' . $dir)) {
                mkdir($jsPath . '/' . $dir, 0755, true);
            }

            $varName = basename($name) . 'Lang';

            if (strpos($name, '/') === false) {
                $varName = 'appLang';
            }

            if (!@file_put_contents($filePath, 'var ' . $varName . ' = ' . Lang::lang($name)->getJsObject() . ';')) {
                throw new Exception($this->lang->get('CANT_WRITE_FS') . ' ' . $filePath);
            }
        }
    }
}