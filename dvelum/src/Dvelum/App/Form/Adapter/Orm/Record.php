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

namespace Dvelum\App\Form\Adapter\Orm;

use Dvelum\App\Form;
use Dvelum\Orm;

class Record extends Form\Adapter
{
    /**
     * @var Orm\Record $object
     */
    protected $object;

    public function validateRequest(): bool
    {
        if (empty($this->config->get('orm_object'))) {
            throw new \Exception(get_called_class() . ': orm_object is not set');
        }

        $this->object = null;

        $id = $this->request->post(
            $this->config->get('idField'),
            $this->config->get('idFieldType'),
            $this->config->get('idFieldDefault')
        );
        $shard = $this->request->post(
            $this->config->get('shardField'),
            $this->config->get('shardFieldType'),
            $this->config->get('shardFieldDefault')
        );

        if (empty($id)) {
            $id = null;
        }

        if (empty($shard)) {
            $shard = null;
        }

        try {
            /**
             * @var Orm\Record $obj
             */
            $obj = Orm\Record::factory($this->config->get('orm_object'), $id, $shard);
        } catch (\Exception $e) {
            $this->errors[] = new Form\Error($this->lang->get('CANT_EXEC'), null, 'init_object');
            return false;
        }

        $posted = $this->request->postArray();

        $fields = $this->getFields($obj);

        $objectConfig = $obj->getConfig();

        foreach ($fields as $name) {
            /*
             * skip primary field
             */
            if ($name == $this->config->get('idField')) {
                continue;
            }


            $field = $objectConfig->getField($name);


            if ($field->isRequired() && !$objectConfig->getField($name)->isSystem(
                ) && (!isset($posted[$name]) || !strlen($posted[$name]))) {
                $this->errors[] = new Form\Error($this->lang->get('CANT_BE_EMPTY'), $name);
                continue;
            }

            if ($field->isBoolean() && !isset($posted[$name])) {
                $posted[$name] = false;
            }

            if (($field->isNull() || $field->isDateField()) && isset($posted[$name]) && empty($posted[$name])) {
                $posted[$name] = null;
            }


            if (!array_key_exists($name, $posted)) {
                continue;
            }

            if (!$id && ((is_string($posted[$name]) && !strlen((string)$posted[$name])) || (is_array(
                $posted[$name]
            ) && empty($posted[$name]))) && $field->hasDefault()) {
                continue;
            }

            try {
                $obj->set($name, $posted[$name]);
            } catch (\Exception $e) {
                $this->errors[] = new Form\Error($this->lang->get('INVALID_VALUE'), $name);
            }
        }

        if (!empty($this->errors)) {
            return false;
        }

        if ($this->config->get('validateUnique')) {
            $errorList = $obj->validateUniqueValues();
            if (!empty($errorList)) {
                foreach ($errorList as $field) {
                    $this->errors[] = new Form\Error($this->lang->get('SB_UNIQUE'), $field);
                }
                return false;
            }
        }

        if ($id) {
            $obj->setId($id);
        }


        $this->object = $obj;
        return true;
    }

    protected function getFields(Orm\RecordInterface $object): array
    {
        return $object->getFields();
    }

    /**
     * @return Orm\Record
     */
    public function getData(): Orm\Record
    {
        return $this->object;
    }
}
