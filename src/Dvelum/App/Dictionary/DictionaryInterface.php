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

namespace Dvelum\App\Dictionary;

use Dvelum\Config\ConfigInterface;

interface DictionaryInterface
{
    public function __construct(string $name, ConfigInterface $config);

    /**
     * Get dictionary name
     * @return string
     */
    public function getName(): string;

    /**
     * Check if the key exists in the dictionary
     * @param string $key
     * @return bool
     */
    public function isValidKey(string $key): bool;

    /**
     * Get value by key
     * @param string $key
     * @return string
     */
    public function getValue(string $key): string;

    /**
     * Get dictionary data
     * @return array
     */
    public function getData(): array;

    /**
     * Add a record
     * @param string $key
     * @param string $value
     * @return void
     */
    public function addRecord(string $key, string $value): void;

    /**
     * Delete record by key
     * @param string $key
     * @return void
     */
    public function removeRecord(string $key): void;

    /**
     * Get dictionary as JavaScript code representation
     * @param boolean $addAll - add value 'All' with a blank key,
     * @param boolean $addBlank - add empty value is used in drop-down lists
     * @param string|boolean $allText , optional - text for not selected value
     * @return string
     */
    public function __toJs($addAll = false, $addBlank = false, $allText = false): string;

    /**
     * Get key for value
     * @param $value
     * @param boolean $i case insensitive
     * @return mixed, false on error
     */
    public function getKeyByValue(string $value, $i = false);

    /**
     * Save dictionary
     * @return bool
     */
    public function save() : bool;

}