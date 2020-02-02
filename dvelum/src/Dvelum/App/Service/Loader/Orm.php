<?php
/**
 *  DVelum project https://github.com/dvelum/dvelum , https://github.com/k-samuel/dvelum , http://dvelum.net
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

namespace Dvelum\App\Service\Loader;

use Dvelum\Config;
use Dvelum;

class Orm extends AbstractAdapter
{
    public function loadService() : Dvelum\Orm\Service
    {
        $cache = null;

        if($this->config->offsetExists('cache')){
            $cache = $this->config->get('cache');
        }

        $orm = new Dvelum\Orm\Service(
            Config::storage()->get('orm.php'),
            $this->config->get('dbManager'),
            $this->config->get('appConfig')->get('language'),
            $cache
        );
        return $orm;
    }
}