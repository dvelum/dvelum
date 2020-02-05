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

use Dvelum\Orm\Record\DataModel;
use Dvelum\Service;
use Dvelum\Utils;

/**
 * Database Object class. ORM element.
 * @author Kirill Egorov 2011-2017  DVelum project
 * @package Dvelum\Orm
 */
class Record implements RecordInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var Record\Config
     */
    protected $config;

    protected $id;
    protected $primaryKey;
    protected $data = [];
    protected $updates = [];
    protected $errors = [];

    /**
     * Insert ID
     * @var integer|bool
     */
    protected $insertId = false;

    /**
     * @var Model|\Dvelum\Orm\Distributed\Model
     */
    protected $model;

    /**
     * Loaded version of VC object
     * @var integer
     */
    protected $version = 0;

    /**
     * @var DataModel|null
     */
    protected $dataModel = null;

    /**
     * The object constructor takes its name and identifier,
     * (the parameter is not required), if absent,
     * there will be created a new object. If ORM lacks the object with the specified
     * identifier, an Exception will show up
     * Using this method is highly undesirable,
     * the factory method Db_Object::factory() is more advisable to use
     * @param string $name
     * @param bool|int $id - optional
     * @throws Exception | \Exception
     */
    public function __construct(string $name, $id = false)
    {
        $this->name = strtolower($name);
        $this->id = $id;

        $this->config = Record\Config::factory($name);
        $this->primaryKey = $this->config->getPrimaryKey();

        if ($this->id) {
            $this->loadData();
        }
    }

    /**
     * @return DataModel
     */
    public function getDataModel(): DataModel
    {
        if (empty($this->dataModel)) {
            $this->dataModel = new DataModel();
        }
        return $this->dataModel;
    }

    /**
     * Load object data
     * @throws \Exception
     * @return void
     */
    protected function loadData(): void
    {
        $dataModel = $this->getDataModel();
        $data = $dataModel->load($this);
        $this->setRawData($data);
    }

    /**
     * Set raw data from storage
     * @param array $data
     * @return void
     * @throws \Exception
     */
    public function setRawData(array $data): void
    {
        unset($data[$this->primaryKey]);
        $iv = false;

        if ($this->config->hasEncrypted()) {
            $ivField = $this->config->getIvField();
            if (isset($data[$ivField]) && !empty($data[$ivField])) {
                $iv = $data[$ivField];
            }
        }

        foreach ($data as $field => &$value) {
            $fieldObject = $this->getConfig()->getField((string) $field);

            if ($fieldObject->isBoolean()) {
                if ($value) {
                    $value = true;
                } else {
                    $value = false;
                }
            }

            if ($fieldObject->isEncrypted()) {
                $value = (string)$value;
                if (is_string($iv) && strlen($value) && strlen($iv)) {
                    $value = $this->config->getCryptService()->decrypt($value, $iv);
                }
            }
        }
        unset($value);
        $this->data = $data;
    }

    /**
     * Get object fields
     * @return array
     */
    public function getFields(): array
    {
        return array_keys($this->config->get('fields'));
    }

    /**
     * Get the object data, returns the associative array ‘field name’
     * @param boolean $withUpdates , optional default true
     * @return array
     */
    public function getData($withUpdates = true): array
    {
        $data = $this->data;
        $data[$this->primaryKey] = $this->id;

        if ($withUpdates) {
            foreach ($this->updates as $k => $v) {
                $data[$k] = $v;
            }
        }

        return $data;
    }

    /**
     * Get object name
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get object identifier
     * @return int|bool
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Check if there are object property changes
     * not saved in the database
     * @return bool
     */
    public function hasUpdates(): bool
    {
        return !empty($this->updates);
    }

    /**
     * Get ORM configuration object (data structure helper)
     * @return Record\Config
     */
    public function getConfig(): Record\Config
    {
        return $this->config;
    }

    /**
     * Get updated, but not saved object data
     * @return array
     * @throws Exception
     */
    public function getUpdates(): array
    {
        return $this->updates;
    }

    /**
     * Set the object identifier (existing DB ID)
     * @param mixed $id
     * @return void
     * @throws Exception
     */
    public function setId($id): void
    {
        $this->id = (int)$id;
    }

    /**
     * Commit the object data changes (without saving)
     * @return void
     */
    public function commitChanges(): void
    {
        if (empty($this->updates)) {
            return;
        }

        foreach ($this->updates as $k => $v) {
            $this->data[$k] = $v;
        }

        $this->updates = [];
    }

    /**
     * Check if the object field exists
     * @param string $name
     * @return bool
     */
    public function fieldExists(string $name): bool
    {
        return $this->config->fieldExists($name);
    }

    /**
     * Get the related object name for the field
     * (available if the object field is a link to another object)
     * @param string $field - field name
     * @return string|null
     */
    public function getLinkedObject(string $field): ?string
    {
        $link = $this->config->getField($field)->getLinkedObject();
        if(empty($link)){
            return null;
        }
        return $link;
    }

    /**
     * Check if the listed objects exist
     * @param string $name
     * @param integer|array $ids
     * @return bool
     * @throws \Exception
     */
    static public function objectExists(string $name, $ids): bool
    {
        if (!Record\Config::configExists($name)) {
            return false;
        }

        try {
            $cfg = Record\Config::factory($name);
        } catch (Exception $e) {
            return false;
        }

        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $model = Model::factory($name);
        $data = $model->getItems($ids);

        if (empty($data)) {
            return false;
        }

        $data = Utils::fetchCol($cfg->getPrimaryKey(), $data);

        foreach ($ids as $v) {
            if (!in_array(intval($v), $data, true)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Set the object properties using the associative array of fields and values
     * @param array $values
     * @throws Exception
     * @return void
     */
    public function setValues(array $values): void
    {
        if (!empty($values)) {
            foreach ($values as $k => $v) {
                $this->set($k, $v);
            }
        }
    }

    /**
     * Set the object field val
     * @param string $name
     * @param mixed $value
     * @return bool
     * @throws Exception
     */
    public function set(string $name, $value): bool
    {
        $propConf = $this->config->getFieldConfig($name);
        $validator = $this->getConfig()->getValidator($name);

        $field = $this->getConfig()->getField($name);

        // set null for empty links
        if ($field->isObjectLink() && empty($value)) {
            $value = null;
        }

        // Validate value using special validator
        // Skip validation if value is null and object field can be null
        if ($validator && (!$field->isNull() || !is_null($value)) && !call_user_func_array([$validator, 'validate'], [$value])) {
            throw new Exception('Invalid value for field ' . $name . ' (' . $this->getName() . ')');
        }

        $value = $field->filter($value);
        if (!$field->validate($value)) {
            throw new Exception('Invalid value for field ' . $name . '. ' . $field->getValidationError() . ' (' . $this->getName() . ')');
        }

        if (isset($propConf['db_len']) && $propConf['db_len']) {
            if ($propConf['db_type'] == 'bit' && (strlen($value) > $propConf['db_len'] || strlen($value) < $propConf['db_len'])) {
                throw new Exception('Invalid length for bit value [' . $name . ']  (' . $this->getName() . ')');
            }
        }

        if (array_key_exists($name, $this->data)) {
            if ($field->isBoolean() && intval($this->data[$name]) === intval($value)) {
                unset($this->updates[$name]);
                return true;
            }

            if ($this->data[$name] === $value) {
                unset($this->updates[$name]);
                return true;
            }
        }

        $this->updates[$name] = $value;
        return true;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @throws Exception
     * @return void
     */
    public function __set($key, $value): void
    {
        if ($key === $this->primaryKey) {
            $this->setId($value);
        } else {
            $this->set($key, $value);
        }
    }

    public function __isset($key): bool
    {
        if ($key === $this->primaryKey) {
            return isset($this->id);
        }

        if (!isset($this->data[$key]) && !isset($this->updates[$key])) {
            return false;
        }

        return true;
    }

    /**
     * @param string $key
     * @throws Exception
     * @return mixed
     */
    public function __get($key)
    {
        if ($key === $this->primaryKey) {
            return $this->getId();
        }

        return $this->get($key);
    }

    /**
     * Get the object field value
     * If field value was updated method returns new value
     * otherwise returns old value
     * @param string $name - field name
     * @throws Exception
     * @return mixed
     */
    public function get(string $name)
    {
        if ($name === $this->primaryKey) {
            return $this->getId();
        }

        if (!$this->fieldExists($name)) {
            throw new Exception('Invalid property requested [' . $name . ']');
        }

        $value = null;

        if (isset($this->data[$name])) {
            $value = $this->data[$name];
        }

        if (isset($this->updates[$name])) {
            $value = $this->updates[$name];
        }

        return $value;
    }

    /**
     * Get the initial object field value (received from the database)
     * whether the field value was updated or not
     * @param string $name - field name
     * @throws Exception
     * @return mixed
     */
    public function getOld(string $name)
    {
        if (!$this->fieldExists($name)) {
            throw new Exception('Invalid property requested [' . $name . ']');
        }
        return $this->data[$name];
    }

    /**
     * Add object error message
     * @param string $message
     */
    public function addErrorMessage(string $message): void
    {
        $this->errors[] = $message;
    }

    /**
     * Save changes
     * @param boolean $useTransaction — using a transaction when changing data is optional.
     * If data update in your code is carried out within an external transaction
     * set the value to  false,
     * otherwise, the first update will lead to saving the changes
     * @return int | bool;
     * @throws Exception
     */
    public function save($useTransaction = true)
    {
        $dataModel = $this->getDataModel();
        if ($dataModel->save($this, $useTransaction)) {
            return $this->getId();
        } else {
            return false;
        }
    }

    /**
     * Deleting an object
     * @param bool $useTransaction — using a transaction when changing data is optional.
     * If data update in your code is carried out within an external transaction
     * set the value to  false,
     * otherwise, the first update will lead to saving the changes
     * @return bool - success flag
     */
    public function delete($useTransaction = true): bool
    {
        $dataModel = $this->getDataModel();
        return $dataModel->delete($this, $useTransaction);
    }

    /**
     * Serialize Object List properties
     * @param array $data
     * @return array
     */
    public function serializeLinks(array $data): array
    {
        foreach ($data as $k => $v) {
            if ($this->config->getField($k)->isMultiLink()) {
                unset($data[$k]);
            }
        }
        return $data;
    }

    /**
     * Validate unique fields, object field groups
     * Returns array of errors or null .
     * @return  array | null
     * @throws \Exception
     */
    public function validateUniqueValues(): ?array
    {
        $uniqGroups = [];

        foreach ($this->config->get('fields') as $k => $v) {
            if ($k === $this->primaryKey) {
                continue;
            }

            if (!$this->config->getField($k)->isUnique()) {
                continue;
            }

            $value = $this->get($k);
            if (is_array($value)) {
                $value = serialize($value);
            }

            if (is_array($v['unique'])) {
                foreach ($v['unique'] as $val) {
                    if (!isset($uniqGroups[$val])) {
                        $uniqGroups[$val] = [];
                    }

                    $uniqGroups[$val][$k] = $value;
                }
            } else {
                $v['unique'] = strval($v['unique']);

                if (!isset($uniqGroups[$v['unique']])) {
                    $uniqGroups[$v['unique']] = [];
                }
                $uniqGroups[$v['unique']][$k] = $value;
            }
        }

        if (empty($uniqGroups)) {
            return null;
        }

        $dataModel = $this->getDataModel();
        return $dataModel->validateUniqueValues($this, $uniqGroups);
    }

    /**
     * Convert object into string representation
     * @return string
     */
    public function __toString(): string
    {
        return strval($this->getId());
    }

    /**
     * Get object title
     * @return string
     * @throws \Exception
     */
    public function getTitle(): string
    {
        return Model::factory($this->getName())->getTitle($this);
    }

    /**
     * Factory method of object creation is preferable to use, cf. method  __construct() description
     * @param string $name
     * @param int|int[]|bool $id , optional default false
     * @param string|bool $shard
     * @throws \Exception
     * @return RecordInterface|RecordInterface[]
     */
    static public function factory(string $name, $id = false, $shard = false)
    {
        /**
         * @var \Dvelum\Orm\Service $service
         */
        $service = Service::get('orm');
        return $service->record($name, $id, $shard);
    }


    /**
     * Get errors
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }


    /**
     * Unpublish VC object
     * @param bool $useTransaction — using a transaction when changing data is optional.
     * @return bool
     */
    public function unpublish($useTransaction = true): bool
    {
        $dataModel = $this->getDataModel();
        return $dataModel->unpublish($this, $useTransaction);
    }

    /**
     * Publish VC object
     * @param int|null $version - optional, default current version
     * @param bool $useTransaction — using a transaction when changing data is optional.
     * @return bool
     * @throws \Exception
     */
    public function publish($version = null, $useTransaction = true): bool
    {
        $dataModel = $this->getDataModel();
        if(empty($version)){
            $version = null;
        }
        return $dataModel->publish($this, $version, $useTransaction);
    }

    /**
     * Get loaded version
     * @return int
     */
    public function getVersion(): int
    {
        return $this->version;
    }

    /**
     * Load version
     * @param int $vers
     * @return bool
     * @throws \Exception
     */
    public function loadVersion(int $vers): bool
    {
        $dataModel = $this->getDataModel();
        return $dataModel->loadVersion($this, $vers);
    }

    /**
     * Reject changes
     */
    public function rejectChanges(): void
    {
        $this->updates = [];
    }

    /**
     * Save object as new version
     * @param bool $useTransaction — using a transaction when changing data is optional.
     * @throws \Exception
     * @return bool
     */
    public function saveVersion(bool $useTransaction = true): bool
    {
        if (!$this->config->isRevControl()) {
            return (bool) $this->save($useTransaction);
        }
        $dataModel = $this->getDataModel();
        return $dataModel->saveVersion($this, $useTransaction);
    }

    /**
     * Set insert id for object (Should not exist in the database)
     * @param int $id
     */
    public function setInsertId($id)
    {
        $this->insertId = $id;
    }

    /**
     * Get insert ID
     * @return int|bool
     */
    public function getInsertId()
    {
        return $this->insertId;
    }

    /**
     * Check DB object class
     * @param string $name
     * @return bool
     */
    public function isInstanceOf(string $name): bool
    {
        $name = strtolower($name);
        return $name === $this->getName();
    }

    /**
     * Set data version
     * @param int $version
     */
    public function setVersion(int $version): void
    {
        $this->version = $version;
    }
}
