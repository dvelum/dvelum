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
namespace  Dvelum\Db;

use Dvelum\Config\ConfigInterface;

interface ManagerInterface extends \Db_Manager_Interface
{
    /**
     * Get Database connection
     * @param string $name
     * @param null|string $workMode
     * @param null|string $shard
     * @return Adapter
     */
    public function getDbConnection(string $name, ?string $workMode = null, ?string $shard = null) : Adapter;
    /**
     * Get DB connection config
     * @param string $name
     * @throws \Exception
     * @return ConfigInterface
     */
    public function getDbConfig(string $name) : ConfigInterface;
}