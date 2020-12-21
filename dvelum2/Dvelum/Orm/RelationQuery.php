<?php
/**
 *  DVelum project https://github.com/dvelum/dvelum
 *  Copyright (C) 2011-2020 Kirill Yegorov
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

use Dvelum\Orm\Model\Query;
use Dvelum\Orm\Record\Config;

class RelationQuery
{
    /**
     * @var string $objectName
     */
    private $objectName;
    /**
     * @var array
     */
    private $queryConfig;

    /**
     * RelationQuery constructor.
     * @param string $objectName
     */
    public function __construct(string $objectName)
    {
        $this->objectName = $objectName;
    }

    /**
     *  Set relations configuration
     * @param array $config
     */
    public function setConfig(array $config) : void
    {
        $this->queryConfig = $config;
    }

    /**
     *  Apply relation joins to Query object
     * @param Query $query
     * @throws Exception
     */
    public function applyToQuery(Query $query) : void
    {
        $tableAlias = $query->getTableAlias();
        if(empty($tableAlias)){
            $tableAlias = $this->objectName;
            $query->tableAlias($tableAlias);
        }
       $this->processNodeList($query, $this->queryConfig, $this->objectName, $tableAlias);
    }

    /**
     * @param Query $query
     * @param array $list
     * @param string $objectName
     * @param string $tableAlias
     * @throws Exception
     */
    private function processNodeList(Query $query, array $list, string $objectName, string $tableAlias) : void
    {
        foreach ($list as $field => $info){
            $this->processNode($query, $objectName, $field, $info, $tableAlias);
        }
    }

    /**
     * @param Query $query
     * @param string $object
     * @param string $field
     * @param array $node
     * @param string|null $parentAlias
     * @throws Exception
     */
    private function processNode(Query $query, string $object, string $field, array $node, string $parentAlias = null): void
    {
        $objectConfig = Config::factory($object);
        $fieldConfig = $objectConfig->getField($field);
        if(!$fieldConfig->isLink()){
            throw new Exception('Relation query '.$object.'->'.$field.' is not link field');
        }
        $relatedObject = $fieldConfig->getLinkedObject();
        $relatedObjectConfig = Config::factory($relatedObject);
        $relatedModel = Model::factory($relatedObject);
        $with = [];
        $joinType = 'inner';
        $tableAlias = $object.'_'.$field;

        if(!isset($node['fields'])){
            throw new Exception('Relation query '.$object.'->'.$field.' fields config is not set');
        }
        $fields = $node['fields'];

        if(isset( $node['table_alias'])){
            $tableAlias =  $node['table_alias'];
        }

        if(isset($node['join_type'])){
            $joinType = $node['join_type'];
        }

        if(isset($node['with'])){
            $with = $node['with'];
        }

        $query->addJoin([
             'joinType' => $joinType,
             'table' => [$tableAlias => $relatedModel->table()],
             'fields' => $fields,
             'condition'=> '`'.$parentAlias.'`.`'.$field.'` = `'.$tableAlias.'`.`'.$relatedObjectConfig->getPrimaryKey().'`'
        ]);

        if(!empty($with)){
            $this->processNodeList($query, $with, $relatedObject, $tableAlias);
        }
    }
}
