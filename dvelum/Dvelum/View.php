<?php
/*
 * DVelum project http://code.google.com/p/dvelum/ , http://dvelum.net
 * Copyright (C) 2011-2012  Kirill A Egorov
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

namespace Dvelum;

use Dvelum\Template\Engine\EngineInterface;
use Dvelum\Template\Storage;

/**
 * View class
 * @author Kirill A Egorov 2011
 */
class View
{
    /**
     * @return EngineInterface
     * @throws \Exception
     */
    static public function factory() : EngineInterface
    {
        /**
         * Runtime call optimization
         * @var Template\Service $service
         */
        static $service = false;
        if(empty($service)){
            $service = Service::get('template');
        }
        return $service->getTemplate();
    }

    /**
     * Get Templates storage
     * @return Storage
     */
    static public function storage() : Storage
    {
        static $store = false;

        if(!$store){
            $store = new Storage();
        }
        return $store;
    }
}