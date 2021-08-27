<?php
/*
 * DVelum project https://github.com/dvelum/dvelum , http://dvelum.net
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

namespace Dvelum\BackgroundTask\Launcher;

use Dvelum\BackgroundTask\Launcher;

/**
 * Task launcher
 * @package Bgtask
 */
abstract class Local extends Launcher
{
    public function __construct()
    {
        ini_set('max_execution_time', 0);
        ini_set('ignore_user_abort', 'On');
        session_write_close();
    }
}
