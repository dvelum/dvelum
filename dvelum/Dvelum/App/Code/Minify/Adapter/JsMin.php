<?php
/**
 *  DVelum project http://code.google.com/p/dvelum/ , https://github.com/k-samuel/dvelum , http://dvelum.net
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
 *
 */
declare(strict_types=1);

namespace Dvelum\App\Code\Minify\Adapter;


class JsMin implements AdapterInterface
{
    public function minify(string $source): string
    {
        return \Code_Js_Minify::minify($source);
    }

    /**
     * Combine and minify code files
     * @param array $files
     * @param string $toFile
     * @return bool
     */
    public function minifyFiles(array $files, string $toFile): bool
    {
        $source = '';

        foreach ($files as $file){
            $source.= file_get_contents($file)."\n";
        }
        file_put_contents($toFile , \Code_Js_Minify::minify($source));
        return true;
    }
}
