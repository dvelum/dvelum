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

use Dvelum\Orm\Model;
use Dvelum\Orm\Record\Config;

/**
 * Class Field
 * @package Dvelum\Orm\Record\Config
 */
class ForeignKey
{
    /**
     * Check if Foreign keys can be used
     * @return bool
     * @throws \Exception
     */
    public function canUseForeignKeys(Config $config) : bool
    {
        $configData = $config->getConfig();
        if($configData->offsetExists('disable_keys') && $configData->get('disable_keys'))
            return false;

        if(!$config->isTransact())
            return false;

        return true;
    }
    /**
     * Get list of foreign keys
     * @param Config $config
     * @return array
     * array(
     * 	array(
     *      'curDb' => string,
     * 		'curObject' => string,
     * 		'curTable' => string,
     *		'curField'=> string,
     *		'isNull'=> boolean,
     *		'toDb'=> string,
     *		'toObject'=> string,
     *		'toTable'=> string,
     *		'toField'=> string,
     *      'onUpdate'=> string
     *      'onDelete'=> string
     *   ),
     *  ...
     *  )
     * @throws \Exception
     */
    public function getForeignKeys(Config $config) : array
    {
        if(!$this->canUseForeignKeys($config)){
            return [];
        }

        $curModel = Model::factory($config->getName());
        $curDb = $curModel->getDbConnection();
        $curDbCfg = $curDb->getConfig();

        $links = $config->getLinks([Config::LINK_OBJECT]);

        if(empty($links))
            return [];

        $keys = [];
        foreach ($links as $object=>$fields)
        {
            $oConfig = Config::factory($object);
            /*
             *  Only InnoDb implements Foreign Keys
             */
            if(!$oConfig->isTransact()){
                continue;
            }

            $oModel = Model::factory($object);

            /*
             * Foreign keys are only available for objects with the same database connection
             */
            if($curDb !== $oModel->getDbConnection()){
                continue;
            }

            foreach ($fields as $name => $linkType) {
                $field = $config->getField($name);

                if ($field->isRequired()) {
                    $onDelete = 'RESTRICT';
                } else {
                    $onDelete = 'SET NULL';
                }

                $keys[] = array(
                    'curDb' => $curDbCfg['dbname'],
                    'curObject' => $config->getName(),
                    'curTable' => $curModel->table(),
                    'curField' => $name,
                    'toObject' => $object,
                    'toTable' => $oModel->table(),
                    'toField' => $oConfig->getPrimaryKey(),
                    'toDb' => $curDbCfg['dbname'],
                    'onUpdate' => 'CASCADE',
                    'onDelete' => $onDelete
                );
            }
        }
        return $keys;
    }
}