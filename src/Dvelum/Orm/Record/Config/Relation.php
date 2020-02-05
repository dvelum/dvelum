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
use Dvelum\Orm\Exception;
use Dvelum\Orm\Record\Config;

/**
 * Class Field
 * @package Dvelum\Orm\Record\Config
 */
class Relation
{

    /**
     * Check if object has ManyToMany relations
     * @param Config $config
     * @return bool
     * @throws Exception
     */
    public function hasManyToMany(Config $config) : bool
    {
        $relations = $this->getManyToMany($config);
        if(!empty($relations)){
            return true;
        }
        return false;
    }

    /**
     * Get manyToMany relations
     * @param Config $config
     * @return array
     * @throws \Exception
     */
    public function getManyToMany(Config $config) : array
    {
        $result = [];
        $fieldConfigs = $config->getFieldsConfig();
        foreach($fieldConfigs as $field=>$cfg)
        {
            if(isset($cfg['type']) && $cfg['type']==='link'
                && isset($cfg['link_config']['link_type'])
                && $cfg['link_config']['link_type'] == Config::LINK_OBJECT_LIST
                && isset($cfg['link_config']['object'])
                && isset($cfg['link_config']['relations_type'])
                && $cfg['link_config']['relations_type'] == Config::RELATION_MANY_TO_MANY
            ){
                $result[$cfg['link_config']['object']][$field] = Config::RELATION_MANY_TO_MANY;
            }
        }
        return $result;
    }

    /**
     * Get name of relations Record
     * @param Config $config
     * @param string $field
     * @return bool|string
     * @throws Exception
     */
    public function getRelationsObject(Config $config, string $field)
    {
        $cfg = $config->getFieldConfig($field);

        if(isset($cfg['type']) && $cfg['type']==='link'
            && isset($cfg['link_config']['link_type'])
            && $cfg['link_config']['link_type'] == Config::LINK_OBJECT_LIST
            && isset($cfg['link_config']['object'])
            && isset($cfg['link_config']['relations_type'])
            && $cfg['link_config']['relations_type'] == Config::RELATION_MANY_TO_MANY
        ){
            return $config->getName().'_'.$field.'_to_'.$cfg['link_config']['object'];
        }
        return false;
    }

    /**
     * Get a list of fields linking to external objects
     * @param Config $config
     * @param array $linkTypes  - optional link type filter
     * @param boolean $groupByObject - group field by linked object, default true
     * @return array  [objectName=>[field => link_type]] | [field =>["object"=>objectName,"link_type"=>link_type]]
     * @throws \Exception
     */
    public function getLinks(Config $config, $linkTypes = [Orm\Record\Config::LINK_OBJECT, Orm\Record\Config::LINK_OBJECT_LIST], $groupByObject = true) : array
    {
        $data = [];
        $fields = $config->getFieldsConfig(true);
        foreach ($fields as $name=>$cfg)
        {
            if(isset($cfg['type']) && $cfg['type']==='link'
                && isset($cfg['link_config']['link_type'])
                && in_array($cfg['link_config']['link_type'], $linkTypes , true)
                && isset($cfg['link_config']['object'])
            ){
                if($groupByObject)
                    $data[$cfg['link_config']['object']][$name] = $cfg['link_config']['link_type'];
                else
                    $data[$name] = ['object'=>$cfg['link_config']['object'],'link_type'=>$cfg['link_config']['link_type']];
            }
        }
        return $data;
    }
}