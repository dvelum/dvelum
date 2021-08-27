<?php

/**
 * DVelum project https://github.com/dvelum/dvelum , https://github.com/k-samuel/dvelum , http://dvelum.net
 * Copyright (C) 2011-2017  Kirill Yegorov
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

namespace Dvelum\App\Module\Manager;

use Dvelum\App\Module\Manager;
use Dvelum\File;
use Dvelum\Config;

class Frontend extends Manager
{
    protected $mainConfigKey = 'frontend_modules';

    /**
     * Get list of Controllers
     * @return array
     */
    public function getControllers(): array
    {
        $autoloadCfg = Config::storage()->get('autoloader.php');
        $paths = $autoloadCfg['paths'];

        $dirs = $this->appConfig->get('frontend_controllers_dirs');

        $data = [];

        foreach ($paths as $path) {
            if (basename($path) === 'modules') {
                $folders = File::scanFiles($path, false, true, File::DIRS_ONLY);

                if (empty($folders)) {
                    continue;
                }

                foreach ($folders as $item) {
                    foreach ($dirs as $dir) {
                        if (!is_dir($item . '/' . $dir)) {
                            continue;
                        }
                        $prefix = str_replace('/', '_', ucfirst(basename($item)) . '_' . $dir . '_');
                        $this->findControllers($item . '/' . $dir, [], $data, $prefix);
                    }
                }
            } else {
                foreach ($dirs as $dir) {
                    if (!is_dir($path . '/' . $dir)) {
                        continue;
                    }
                    $prefix = str_replace('/', '_', $dir . '_');
                    $this->findControllers($path . '/' . $dir, [], $data, $prefix);
                }
            }
        }
        return array_values($data);
    }

    /**
     * Update module data
     * @param string $name
     * @param array $data
     * @return bool
     */
    public function updateModule(string $name, array $data): bool
    {
        if ($name !== $data['code']) {
            $this->modulesLocale->remove($name);
            $this->config->remove($name);
        }

        if (isset($data['title'])) {
            $this->modulesLocale->set($data['code'], $data['title']);
            if (!$this->localeStorage->save($this->modulesLocale)) {
                return false;
            }
            unset($data['title']);
        }
        $this->config->set($data['code'], $data);
        return $this->save();
    }

    /**
     * Get modules list
     * @return array
     */
    public function getList(): array
    {
        $list = parent::getList();
        if (!empty($list)) {
            foreach ($list as $k => &$v) {
                $cfg['dist'] = true;

                if ($this->curConfig && $this->curConfig->offsetExists($k)) {
                    $cfg['dist'] = false;
                }

                $v['id'] = $v['code'];
            }
        }
        return $list;
    }
}
