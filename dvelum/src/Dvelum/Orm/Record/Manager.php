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

namespace Dvelum\Orm\Record;

use Dvelum\File;
use Dvelum\Service;

/**
 * Db_Object Manager class
 * @package Db
 * @subpackage Db_Object
 * @author Kirill A Egorov kirill.a.egorov@gmail.com
 * @copyright Copyright (C) 2011-2012  Kirill A Egorov,
 * DVelum project https://github.com/dvelum/dvelum , http://dvelum.net
 * @license General Public License version 3
 */
class Manager
{
    static protected $objects = null;

    /**
     * Get list of registered objects (names only)
     * @return array
     */
    public function getRegisteredObjects(): ?array
    {
        if (is_null(self::$objects)) {
            self::$objects = [];
            $paths = \Dvelum\Config::storage()->getPaths();

            $list = [];

            /**
             * @var \Dvelum\Orm\Service $ormService
             */
            $ormService = Service::get('orm');
            $cfgPath = $ormService->getConfigSettings()->get('configPath');

            foreach ($paths as $path) {
                if (!file_exists($path . $cfgPath)) {
                    continue;
                }

                $items = File::scanFiles($path . $cfgPath, array('.php'), false, File::FILES_ONLY);

                if (!empty($items)) {
                    foreach ($items as $o) {
                        $baseName = substr(basename($o), 0, -4);
                        if (!isset($list[$baseName])) {
                            self::$objects[] = $baseName;
                            $list[$baseName] = true;
                        }
                    }
                }
            }
        }
        return self::$objects;
    }

    /**
     * Check if object exists
     * @param string $name
     * @return bool
     */
    public function objectExists(string $name): bool
    {
        $list = $this->getRegisteredObjects();
        if(empty($list)){
            return false;
        }
        return in_array(strtolower($name), $list, true);
    }
}