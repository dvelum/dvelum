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

namespace Dvelum\Orm\Record\Config;

use Dvelum\Config\Storage\StorageInterface;
use Dvelum\Lang;


class Translator
{
    protected $commonPath = '';
    protected $localesDir = '';

    protected $translation = false;
    /**
     * @var \Dvelum\Lang\Dictionary | false
     */
    protected $lang = false;

    /**
     * @param string $commonPath - path to translation Array config
     * @param string $localesDir - locales directory (relative)
     */
    public function __construct(string $commonPath, string $localesDir)
    {
        $this->commonPath = $commonPath;
        $this->localesDir = $localesDir;
    }

    /**
     * Get object fields translation
     * @param string $objectName
     * @param bool $force
     * @return array
     */
    public function getTranslation(string $objectName, $force = false): array
    {
        if (!$this->translation || $force) {
            $this->translation = Lang::storage()->get($this->commonPath, true, true)->__toArray();
        }

        if (!isset($this->translation[$objectName])) {
            $localFile = $this->localesDir . strtolower($objectName) . '.php';

            if (Lang::storage()->exists($localFile)) {
                $this->translation[$objectName] = Lang::storage()->get($localFile, true, true)->__toArray();
            }
        }

        if (isset($this->translation[$objectName])) {
            return $this->translation[$objectName];
        } else {
            return [];
        }
    }

    /**
     * Get translations storage
     * @return StorageInterface
     */
    public function getStorage(): StorageInterface
    {
        return Lang::storage();
    }

    /**
     * Get common config path
     * @return string
     */
    public function getcommonConfigPath(): string
    {
        return $this->commonPath;
    }

    /**
     * Translate Object config
     * @param string $objectName
     * @param array $objectConfig
     * @throws \Exception
     */
    public function translate(string $objectName, & $objectConfig)
    {
        $translation = $this->getTranslation($objectName);

        if (!empty($translation)) {
            if (isset($translation['title']) && strlen($translation['title']))
                $objectConfig['title'] = $translation['title'];
            else
                $objectConfig['title'] = $objectName;

            if (isset($translation['fields']) && is_array($translation['fields']))
                $fieldTranslates = $translation['fields'];
        } else {
            if (isset($translation['title']) && strlen($translation['title']))
                $objectConfig['title'] = $translation['title'];
            else
                $objectConfig['title'] = $objectName;
        }

        foreach ($objectConfig['fields'] as $k => &$v) {
            if (isset($v['lazyLang']) && $v['lazyLang']) {
                if (!$this->lang)
                    $this->lang = Lang::lang();

                if (isset($v['title']))
                    $v['title'] = $this->lang->get($v['title']);
                else
                    $v['title'] = '';
            } elseif (isset($fieldTranslates[$k]) && strlen($fieldTranslates[$k])) {
                $v['title'] = $fieldTranslates[$k];
            } elseif (!isset($v['title']) || !strlen($v['title'])) {
                $v['title'] = $k;
            }
        }
        unset($v);
    }

    /**
     * Save object translation
     * @param string $objectName
     * @param array $translationData
     * @return bool
     */
    public function save(string $objectName, array $translationData): bool
    {
        $localFile = $this->localesDir . strtolower($objectName) . '.php';

        if (!Lang::storage()->exists($localFile)) {
            if (!Lang::storage()->create($localFile)) {
                return false;
            }
        }

        $configFile = Lang::storage()->get($localFile);
        $configFile->setData($translationData);

        if (empty($configFile)) {
            return false;
        }

        if (!$this->getStorage()->save($configFile)) {
            return false;
        }

        $common = Lang::storage()->get($this->commonPath, true, true);

        if ($common->offsetExists($objectName)) {
            $common->offsetUnset($objectName);
            if (!$this->getStorage()->save($common)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Remove object translations
     * @param string $objectName
     * @param bool $checkOnly - only check filesystem permission to write file
     * @return bool
     */
    public function removeObjectTranslation(string $objectName, bool $checkOnly = false): bool
    {
        $localFile = $this->getStorage()->getWrite() . $this->localesDir . strtolower($objectName) . '.php';

        if (file_exists($localFile)) {
            if ($checkOnly) {
                return is_writable($localFile);
            } else {
                try {
                    return unlink($localFile);
                } catch (\Error $e) {
                    return false;
                }
            }
        }
        return true;
    }
}