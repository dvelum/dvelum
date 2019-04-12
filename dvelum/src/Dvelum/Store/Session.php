<?php
/**
 * DVelum project https://github.com/dvelum/dvelum , https://github.com/k-samuel/dvelum , http://dvelum.net
 * Copyright (C) 2010-2019  Kirill Yegorov
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

namespace Dvelum\Store;

/**
 * Session storage
 * @author Kirill A Egorov 2008
 * @package Store
 */
class Session extends Local
{
    protected $prefix = 'sc_';
    /**
     * (non-PHPdoc)
     * @see www/library/Store/Store_Local#_storageConnect()
     */
    protected function storageConnect()
    {
        @session_start();

        if(!isset($_SESSION[$this->prefix][$this->name]))
            $_SESSION[$this->prefix][$this->name] = [];

        $this->storage = &$_SESSION[$this->prefix][$this->name];
    }
}