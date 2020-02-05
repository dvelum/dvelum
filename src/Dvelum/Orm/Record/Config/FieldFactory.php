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

use Dvelum\Orm;
use Dvelum\Orm\Record\Config;

/**
 * Class Field
 * @package Dvelum\Orm\Record\Config
 */
class FieldFactory
{
    public static function getField(Config $config, string $fieldName): Field
    {
        $fields = $config->getConfig()->get('fields');

        if(!isset($fields[$fieldName])){
            throw new Orm\Exception('Undefined field ' . $config->getName() . '.' . $fieldName);
        }

        $configData = $fields[$fieldName];
        $configData['name'] = $fieldName;
        $fieldClass = 'Field';

        //detect field type
        if(!isset($configData['db_type'])){
            throw new Orm\Exception('Undefined db_type for field ' . $config->getName() . '.' . $fieldName);
        }
        $dbType = $configData['db_type'];

        if (isset($configData['type']) && $configData['type'] === 'link' && isset($configData['link_config']) && isset($configData['link_config']['link_type'])) {
            switch ($configData['link_config']['link_type']) {
                case Orm\Record\Config::LINK_OBJECT;
                    $fieldClass = 'ObjectItem';
                    break;
                case Orm\Record\Config::LINK_OBJECT_LIST;
                    $fieldClass = 'ObjectList';
                    break;
                case 'dictionary';
                    $fieldClass = 'Dictionary';
                    break;
            }
        } else {
            if (in_array($dbType, Orm\Record\Builder::$intTypes, true)) {
                $fieldClass = 'Integer';
            } elseif (in_array($dbType, Orm\Record\Builder::$charTypes, true)) {
                $fieldClass = 'Varchar';
            } elseif (in_array($dbType, Orm\Record\Builder::$textTypes, true)) {
                $fieldClass = 'Text';
            } elseif (in_array($dbType, Orm\Record\Builder::$floatTypes, true)) {
                $fieldClass = 'Floating';
            } else {
                $fieldClass = $dbType;
            }
        }
        $fieldClass = 'Dvelum\\Orm\\Record\\Config\\Field\\' . ucfirst((string)$fieldClass);

        if (class_exists($fieldClass)) {
            $field = new $fieldClass($configData);
        } else {
            $field = new Config\Field($configData);
        }
        return $field;
    }
}