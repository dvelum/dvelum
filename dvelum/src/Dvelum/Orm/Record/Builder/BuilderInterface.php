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

namespace Dvelum\Orm\Record\Builder;
use Dvelum\Config\ConfigInterface;

/**
 * interface BuilderInterface
 * @package Dvelum\Orm\Record\Builder
 */
interface BuilderInterface
{
    /**
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config);

    /**
     * Get error messages
     * @return array
     */
    public function getErrors();

    /**
     * Check for broken object links
     * @return array
     */
    public function getBrokenLinks() : array;

    /**
     * Check if DB table has correct structure
     * @return bool
     */
    public function validate() : bool;

    /**
     * Get object foreign keys
     * @return array
     */
    public function getOrmForeignKeys() : array;

    /**
     * Get updates information
     * @return array
     */
    public function getRelationUpdates() : array;

    /**
     * Check for broken object links
     * return array | boolean false
     */
    public function hasBrokenLinks();

    /**
     * Create / alter db table
     * @param bool $buildForeignKeys
     * @param bool $buildShards
     * @return boolean
     */
    public function build(bool $buildForeignKeys = true, bool $buildShards = false) : bool;
    /**
     * Build Foreign Keys
     * @param bool $remove - remove keys
     * @param bool $create - create keys
     * @return boolean
     */
    public function buildForeignKeys($remove = true , $create = true) : bool;

    /**
     * Remove object
     * @return bool
     */
    public function remove():bool;
}