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

use \Tree as Tree;

class Format
{
    /**
     * Convert files list into Tree structure
     * @param array $data
     * @return Tree
     */
    static public function fileListToTree(array $data): Tree
    {
        $tree = new Tree();
        foreach ($data as $k => $v) {
            $tmp = explode('/', substr($v, 2));
            for ($i = 0, $s = sizeof($tmp); $i < $s; $i++) {
                if ($i == 0) {
                    $id = $tmp[0];
                    $par = 0;
                } else {
                    $id = implode('/', array_slice($tmp, 0, $i + 1));
                    $par = implode('/', array_slice($tmp, 0, $i));
                }
                $tree->addItem($id, $par, $tmp[$i]);
            }
        }
        return $tree;
    }

    /**
     * Format time
     * @param int $difference
     * @return string
     */
    static public function formatTime(int $difference) : string
    {
        $days = floor($difference / 86400);
        $difference = $difference % 86400;
        $hours = floor($difference / 3600);
        $difference = $difference % 3600;
        $minutes = floor($difference / 60);
        $difference = $difference % 60;
        $seconds = floor($difference);
        if ($minutes == 60) {
            $hours = $hours + 1;
            $minutes = 0;
        }
        $s = '';

        if ($days > 0) {
            $s .= $days . ' days ';
        }

        $s .= str_pad((string) $hours, 2, '0', STR_PAD_LEFT) .
            ':' .
            str_pad((string) $minutes, 2, '0', STR_PAD_LEFT) .
            ':' .
            str_pad((string) $seconds, 2, '0', STR_PAD_LEFT);
        return $s;
    }

    /**
     * Format file size in user friendly
     * @param int $size
     * @return string
     */
    static public function formatFileSize(int $size) : string
    {
        /*
         * 1024 * 1024 * 1024  - Gb
        */
        if ($size > 1073741824) {
            return number_format($size / 1073741824, 1) . ' Gb';
        }
        /*
         * 1024 * 1024 - Mb
        */
        if ($size > 1048576) {
            return number_format($size / 1048576, 1) . ' Mb';
        }
        /*
         * 1024  - Kb
        */
        if ($size > 1024) {
            return number_format($size / 1024, 1) . ' Kb';
        }
        return $size . ' B';
    }
}