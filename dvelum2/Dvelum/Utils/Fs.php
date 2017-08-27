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

namespace Dvelum\Utils;

use Dvelum\File;

class Fs
{
    /**
     * Create class name from file path
     * @param string $path
     * @param bool $check
     * @return null|string
     */
    static public function classFromPath(string $path, bool $check = false) : ?string
    {
        // windows path hack
        $path = str_replace('\\','/', $path);
        $path = str_replace('//','/', $path);

        if(File::getExt($path)!=='.php')
            return null;

        $path = substr($path, 0, -4);

        if(strpos($path , ('../')) === 0)
            $path = substr($path, 3);
        elseif(strpos($path , ('./')) === 0)
            $path = substr($path, 2);
        elseif(strpos($path , '/') === 0)
            $path = substr($path, 1);

        $result = implode('_' , array_map('ucfirst',explode('/', $path)));

        if($check && !class_exists($result)){
            $result = '\\' . str_replace('_','\\', $result);
            if(!class_exists($result)){
                return null;
            }
        }
        return $result;
    }


    /**
     * Create path for cache file
     * @param string $basePath
     * @param string $fileName
     * @return string
     */
    static public function createCachePath(string $basePath, string $fileName): string
    {
        $extension = File::getExt($fileName);

        $str = md5($fileName);
        $len = strlen($str);
        $path = '';
        $count = 0;
        $parts = 0;
        for ($i = 0; $i < $len; $i++) {
            if ($count == 4) {
                $path .= '/';
                $count = 0;
                $parts++;
            }
            if ($parts == 4) {
                break;
            }
            $path .= $str[$i];
            $count++;
        }
        $path = $basePath . $path;

        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }

        return $path . $str . $extension;
    }
}