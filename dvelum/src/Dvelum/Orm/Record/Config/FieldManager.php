<?php
/*
 * DVelum project https://github.com/dvelum/dvelum , https://github.com/k-samuel/dvelum , http://dvelum.net
 * Copyright (C) 2011-2020  Kirill Yegorov
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

namespace Dvelum\Orm\Record\Config;

use Dvelum\Orm\Exception;
use Dvelum\Orm\Record\Config;

class FieldManager
{
    /**
     * Remove field from configuration object
     * @param Config $config
     * @param string $name
     * @throws \Exception
     */
    public function removeField(Config $config, string $name) : void
    {
        $fields = $config->getFieldsConfig();

        if(!isset($fields[$name]))
            return;

        unset($fields[$name]);

        $config->getConfig()->set('fields' , $fields);

        $indexManager = new IndexManager();
        $indexManager->removeFieldIndexes($config, $name);
    }

    /**
     * Rename field
     * @param Config $config
     * @param string $oldName
     * @param string $newName
     * @return void
     * @throws \Exception
     */
    public function renameField(Config $config, string $oldName , string $newName) : void
    {
        $fields = $config->getFieldsConfig();

        if(!isset($fields[$oldName])){
            throw new Exception('Undefined field ' . $config->getName() . '.' . $oldName);
        }

        $fields[$newName] = $fields[$oldName];
        unset($fields[$oldName]);

        $config->getConfig()->set('fields', $fields);

        $indexManager = new IndexManager();
        $indexManager->renameFieldInIndex($config, $oldName, $newName);
    }

    /**
     * Configure the field
     * @param Config $config
     * @param string $field
     * @param array $data
     */
    public function setFieldConfig(Config $config, string $field , array $data) : void
    {
        $cfg = & $config->getConfig()->dataLink();
        $cfg['fields'][$field] = $data;
    }

    /**
     * Update field link, set linked object name
     * @param Config $config
     * @param string $field
     * @param string $linkedObject
     * @return bool
     * @throws \Exception
     */
    public function setFieldLink(Config $config, string $field , string $linkedObject) : bool
    {
        if(!$config->getField($field)->isLink())
            return false;

        $cfg = & $config->getConfig()->dataLink();
        $cfg['fields'][$field]['link_config']['object'] = $linkedObject;
        return true;
    }
}
