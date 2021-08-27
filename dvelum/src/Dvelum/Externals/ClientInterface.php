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

namespace Dvelum\Externals;

use Dvelum\Config\ConfigInterface;
use Dvelum\Lang;

/**
 * Add-ons repository client
 */
interface ClientInterface
{

    public function __construct(ConfigInterface $repoConfig);

    /**
     * Set client language
     * @param string $lang
     */
    public function setLanguage(string $lang): void;

    /**
     * set localization dictionary
     * @param Lang\Dictionary $lang
     * @return void
     */
    public function setLocalization(Lang\Dictionary $lang): void;

    /**
     * Get add-ons list
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function getList(array $params): array;

    /**
     * Download add-on
     * @param string $app
     * @return bool
     * @throws \Exception
     */
    public function download(string $app): bool;

    /**
     * Set tmp dir for downloads
     * @param string $dir
     */
    public function setTmpDir(string $dir): void;

    /**
     * Remove package
     * @param string $app
     * @return bool
     * @throws \Exception
     */
    public function remove(string $app): bool;

}