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

namespace Dvelum\App\Console\Generator;

use Dvelum\App\Console;
use Dvelum\Orm;
use Dvelum\Config;
use Dvelum\Lang;

class Models extends Console\Action
{
    public function action(): bool
    {
        $dbObjectManager = new Orm\Record\Manager();
        $modelPath = Config::storage()->get('main.php')->get('local_models');

        echo 'GENERATE MODELS' . PHP_EOL;

        foreach ($dbObjectManager->getRegisteredObjects() as $object) {
            $list = explode('_', $object);
            $list = array_map('ucfirst', $list);
            $class = 'Model_' . implode('_', $list);

            $path = str_replace(['_', '\\'], '/', $class);
            $namespace = str_replace('/', '\\' , dirname($path));
            $fileName = basename($path);

            $path = $modelPath . $path . '.php';

            if (!class_exists($class)) {
                echo $namespace . '\\' . $fileName . "\n";
                $dir = dirname($path);

                if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
                    echo Lang::lang()->get('CANT_WRITE_FS') . ' ' . $dir;
                    return false;
                }

                $data = '<?php ' . PHP_EOL
                    . 'namespace ' . $namespace . ';' . PHP_EOL . PHP_EOL
                    . 'use Dvelum\\Orm\\Model;' . PHP_EOL . PHP_EOL
                    . 'class ' . $fileName . ' extends Model {}';

                if (!file_put_contents($path, $data)) {
                    echo Lang::lang()->get('CANT_WRITE_FS') . ' ' . $path;
                    return false;
                }
            }
        }
        return true;
    }
}