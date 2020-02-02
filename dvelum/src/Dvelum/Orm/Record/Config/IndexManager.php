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

use Dvelum\Orm\Record\Config;

class IndexManager
{
    /**
     * Check if Index exists
     * @param Config $config
     * @param string $index
     * @return bool
     */
    public function indexExists(Config $config ,string $index) : bool
    {
        return isset($config->getConfig()['indexes'][$index]);
    }

    /**
     * Remove indexes for field
     * @param Config $config
     * @param string $fieldName
     * @throws \Exception
     */
    public function removeFieldIndexes(Config $config, string $fieldName) : void
    {
        $indexes = $config->getIndexesConfig();
        /**
         * Check for indexes for field
         */
        foreach ($indexes as $index => &$item)
        {
            if(isset($item['columns']) && !empty($item['columns']))
            {
                /*
                 * Remove field from index
                 */
                foreach ($item['columns'] as $id=>$value){
                    if($value === $fieldName){
                        unset($item['columns'][$id]);
                    }
                }
                /*
                 * Remove empty index
                 */
                if(empty($item['columns'])){
                    unset($indexes[$index]);
                }
            }
        }
        $config->getConfig()->set('indexes', $indexes);
    }

    /**
     * Remove index from record configuration
     * @param Config $config
     * @param string $indexName
     * @throws \Exception
     */
    public function removeIndex(Config $config, string $indexName) : void
    {
        $indexes = $config->getIndexesConfig();

        if(!isset($indexes[$indexName]))
            return;

        unset($indexes[$indexName]);

        $config->getConfig()->set('indexes' , $indexes);
    }

    /**
     * Rename field in index configuration
     * @param Config $config
     * @param string $oldName
     * @param string $newName
     * @throws \Exception
     */
    public function renameFieldInIndex(Config $config, string $oldName, string $newName) : void
    {
        $indexes = $config->getIndexesConfig();
        /**
         * Check for indexes for field
         */
        foreach ($indexes as $index => &$item)
        {
            if(isset($item['columns']) && !empty($item['columns']))
            {
                /*
                 * Rename index link
                 */
                foreach ($item['columns'] as $id => &$value){
                    if($value === $oldName){
                        $value = $newName;
                    }
                }unset($value);
            }
        }
        $config->getConfig()->set('indexes', $indexes);
    }

    /**
     * Delete distributed index
     * @param Config $config
     * @param string $name
     * @return bool
     * @throws \Exception
     */
    public function removeDistributedIndex(Config $config, string $name) : bool
    {
        $indexes = $config->getDistributedIndexesConfig();

        if(!isset($indexes[$name]) || $indexes[$name]['is_system'])
            return false;

        unset($indexes[$name]);

        $config->getConfig()->set('distributed_indexes' , $indexes);

        return true;
    }

    /**
     * Configure the index
     * @param Config $config
     * @param string $index
     * @param array $data
     * @return void
     * @throws \Exception
     */
    public function setIndexConfig(Config $config, $index , array $data) : void
    {
        $indexes = $config->getIndexesConfig();
        $indexes[$index] = $data;
        $config->getConfig()->set('indexes', $indexes);
    }

    /**
     * Configure distributed index
     * @param Config $config
     * @param string $index
     * @param array $config
     * @throws \Exception
     */
    public function setDistributedIndexConfig(Config $config, string $index, array $data)
    {
        $indexes = $config->getDistributedIndexesConfig();
        $indexes[$index] = $data;
        $config->getConfig()->set('distributed_indexes', $indexes);
    }

}
