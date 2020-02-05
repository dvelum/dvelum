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

namespace Dvelum\Orm;

interface RecordInterface
{

    public function __construct(string $name, $id = false);

    /**
     * Set raw data from storage
     * @param array $data
     * @return void
     */
    public function setRawData(array $data): void;

    /**
     * Get object fields
     * @return array
     */
    public function getFields(): array;

    /**
     * Get the object data, returns the associative array ‘field name’
     * @param boolean $withUpdates , optional default true
     * @return array
     */
    public function getData($withUpdates = true): array;

    /**
     * Get object name
     * @return string
     */
    public function getName(): string;

    /**
     * Get object identifier
     * @return int|false
     */
    public function getId();

    /**
     * Check if there are object property changes
     * not saved in the database
     * @return bool
     */
    public function hasUpdates(): bool;

    /**
     * Get ORM configuration object (data structure helper)
     * @return Record\Config
     */
    public function getConfig(): Record\Config;

    /**
     * Get updated, but not saved object data
     * @return array
     */
    public function getUpdates(): array;

    /**
     * Set the object identifier (existing DB ID)
     * @param integer $id
     * @return void
     */
    public function setId($id): void;

    /**
     * Commit the object data changes (without saving)
     * @return void
     */
    public function commitChanges(): void;

    /**
     * Check if the object field exists
     * @param string $name
     * @return bool
     */
    public function fieldExists(string $name): bool;

    /**
     * Get the related object name for the field
     * (available if the object field is a link to another object)
     * @param string $field - field name
     * @return string|null
     */
    public function getLinkedObject(string $field): ?string;

    /**
     * Set the object properties using the associative array of fields and values
     * @param array $values
     * @throws Exception
     * @return void
     */
    public function setValues(array $values): void;

    /**
     * Set the object field val
     * @param string $name
     * @param mixed $value
     * @return bool
     * @throws Exception
     */
    public function set(string $name, $value): bool;

    /**
     * Get the object field value
     * If field value was updated method returns new value
     * otherwise returns old value
     * @param string $name - field name
     * @throws Exception
     * @return mixed
     */
    public function get(string $name);

    /**
     * Get the initial object field value (received from the database)
     * whether the field value was updated or not
     * @param string $name - field name
     * @throws Exception
     * @return mixed
     */
    public function getOld(string $name);

    /**
     * Save changes
     * @param boolean $useTransaction — using a transaction when changing data is optional.
     * If data update in your code is carried out within an external transaction
     * set the value to  false,
     * otherwise, the first update will lead to saving the changes
     * @return int | boolean;
     */
    public function save($useTransaction = true);

    /**
     * Deleting an object
     * @param boolean $useTransaction — using a transaction when changing data is optional.
     * If data update in your code is carried out within an external transaction
     * set the value to  false,
     * otherwise, the first update will lead to saving the changes
     * @return bool - success flag
     */
    public function delete($useTransaction = true): bool;

    /**
     * Serialize Object List properties
     * @param array $data
     * @return array
     */
    public function serializeLinks(array $data): array;

    /**
     * Validate unique fields, object field groups
     * Returns array of errors  or null .
     * @property boolean $new
     * @return array | null
     */
    public function validateUniqueValues(): ?array;

    /**
     * Convert object into string representation
     * @return string
     */
    public function __toString(): string;

    /**
     * Get object title
     */
    public function getTitle(): string;

    /**
     * Get errors
     * @return array
     */
    public function getErrors(): array;

    /**
     * Unpublish VC object
     * @param bool $useTransaction — using a transaction when changing data is optional.
     * @return bool
     */
    public function unpublish($useTransaction = true): bool;

    /**
     * Publish VC object
     * @param bool|int $version - optional, default current version
     * @param bool $useTransaction — using a transaction when changing data is optional.
     * @return bool
     * @throws Exception
     */
    public function publish($version = false, $useTransaction = true): bool;

    /**
     * Get loaded version
     * @return int
     */
    public function getVersion(): int;

    /**
     * Load version
     * @param int $vers
     * @throws Exception
     * @return bool
     */
    public function loadVersion(int $vers): bool ;

    /**
     * Reject changes
     */
    public function rejectChanges(): void;

    /**
     * Save object as new version
     * @param boolean $useTransaction — using a transaction when changing data is optional.
     * @return bool
     */
    public function saveVersion(bool $useTransaction = true): bool;

    /**
     * Set insert id for object (Should not exist in the database)
     * @param int $id
     */
    public function setInsertId($id);

    /**
     * Get insert ID
     * @return integer
     */
    public function getInsertId();

    /**
     * Check DB object class
     * @param string $name
     * @return bool
     */
    public function isInstanceOf(string $name): bool;

    /**
     * Add error message
     * @param string $message
     */
    public function addErrorMessage(string $message): void;

    /**
     * Set data version identifier
     * @param int $version
     */
    public function setVersion(int $version): void;
}