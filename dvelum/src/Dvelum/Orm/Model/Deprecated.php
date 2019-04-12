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

namespace Dvelum\Orm\Model;

use Dvelum\Orm\Model;
use Dvelum\Db;
use Dvelum\Utils;

/**
 * Backward compatibility for deprecated Model methods
 * @package Dvelum\Orm\Model
 * @deprecated
 */
class Deprecated
{
    protected $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Insert multiple rows (not safe but fast)
     * @param array $data
     * @param int $chunkSize
     * @param bool $ignore - optional default false
     * @return bool
     * @deprecated
     */
    public function multiInsert(array $data, int $chunkSize = 300, bool $ignore = false): bool
    {
        $insert = new Model\Insert($this->model);
        return $insert->bulkInsert($data, $chunkSize, $ignore);
    }

    /**
     * Insert single record on duplicate key update
     * @param array $data
     * @return bool
     * @deprecated
     */
    public function insertOnDuplicateKeyUpdate(array $data): bool
    {
        $insert = new Model\Insert($this->model);
        return $insert->onDuplicateKeyUpdate($data);
    }

    /**
     * Add joins to the query
     * @param Db\Select $sql
     * @param array $joins
     * @deprecated
     */
    public function queryAddJoins(Db\Select $sql, array $joins)
    {
        if (empty($joins)) {
            return;
        }
        $query = new Model\Query($this->model);
        $query->applyJoins($sql, $joins);
    }

    /**
     * @param Db\Select $sql
     * @param array $joins
     * @deprecated
     */
    public function _queryAddJoins(Db\Select $sql, array $joins)
    {
        $this->queryAddJoins($sql, $joins);
    }

    /**
     * Add Like where couse for query
     * @param Db\Select $sql
     * @param string $query
     * @param string $alias - table name alias, optional
     * @return void
     * @deprecated
     */
    public function queryAddQuery(Db\Select $sql, $query, ?string $alias = null): void
    {
        if (empty($query)) {
            return;
        }
        $query = new Model\Query($this->model);
        $query->applySearch($sql, $query, $alias);
    }

    /**
     * @param Db\Select $sql
     * @param string $query
     * @param null|string $alias
     * @deprecated
     */
    public function _queryAddQuery(Db\Select $sql, string $query, ?string $alias): void
    {
        $this->queryAddQuery($sql, $query, $alias);
    }

    /**
     * Get a list of records (is used by CRUD_VC controllers)
     * @param array|bool $params - parameters array('start'=>0,'limit'=>10,'sort'=>'fieldname','dir'=>'DESC')
     * @param array|bool $filters - filters
     * @param string|bool $query — optional string for search
     * @param mixed $fields — optional list of fields
     * @param string|bool $author - optional key for storing entry author id
     * @param string|bool $lastEditor - optional key  for storing the last editor’s ID
     * @param array|bool $joins - optional, inclusion config for Zend_Select:
     * array(
     *          array(
     *                'joinType'=> joinLeft/left, joinRight/right, joinInner/inner
     *                'table' => array / string
     *                'fields => array / string
     *                'condition'=> string
     *          )...
     * )
     * @return array
     * @deprecated
     */
    public function getListVc($params = false, $filters = false, $query = false, $fields = '*', $author = false, $lastEditor = false, $joins = false): array {

        if (is_array($filters) && !empty($filters)) {
            $filters = $this->clearFilters($filters);
        }

        $slave = $this->model->getSlaveDbConnection();

        if ($slave === Model::factory('User')->getSlaveDbConnection()) {
            return $this->getListVcLocal($params, $filters, $query, $fields, $author, $lastEditor, $joins);
        } else {
            return $this->getListVcRemote($params, $filters, $query, $fields, $author, $lastEditor, $joins);
        }
    }

    /**
     * Prepare filter values , clean empty filters
     * @param array $filters
     * @return array
     * @deprecated
     */
    public function clearFilters(array $filters)
    {
        $fields = $this->model->getLightConfig()->get('fields');
        foreach ($filters as $field => $val) {
            if (!($val instanceof Db\Select\Filter) && !is_null($val) && (!is_array($val) && !strlen((string)$val))) {
                unset($filters[$field]);
                continue;
            }

            if (isset($fields[$field]) && isset($fields[$field]['db_type']) && $fields[$field]['db_type'] === 'boolean') {
                $filters[$field] = \Dvelum\Filter::filterValue(\Dvelum\Filter::FILTER_BOOLEAN, $val);
            }
        }
        return $filters;
    }

    /**
     * @param bool $params
     * @param bool $filters
     * @param bool $query
     * @param string $fields
     * @param bool $author
     * @param bool $lastEditor
     * @param array|bool $joins
     * @return array
     * @deprecated
     */
    public function getListVcLocal($params = false, $filters = false, $query = false, $fields = '*', $author = false, $lastEditor = false, $joins = false): array
    {
        $slave = $this->model->getSlaveDbConnection();

        $table = $this->model->table();
        $sql = $slave->select()->from($table, $fields);

        if ($filters) {
            $this->queryAddFilters($sql, $filters);
        }

        if ($author) {
            $this->queryAddAuthor($sql, (string)$author);
        }

        if ($lastEditor) {
            $this->queryAddEditor($sql, $lastEditor);
        }

        if ($query && strlen($query)) {
            $this->queryAddQuery($sql, $query);
        }

        if ($params) {
            $this->queryAddPagerParams($sql, $params);
        }

        if (is_array($joins) && !empty($joins)) {
            $this->queryAddJoins($sql, $joins);
        }

        return $slave->fetchAll($sql);
    }

    /**
     * @param bool $params
     * @param bool $filters
     * @param bool $query
     * @param string $fields
     * @param bool $author
     * @param bool $lastEditor
     * @param array|bool $joins
     * @return array
     * @deprecated
     */
    public function getListVcRemote($params = false, $filters = false, $query = false, $fields = '*', $author = false, $lastEditor = false, $joins = false): array
    {
        if ($fields !== '*') {
            if ($author) {
                if (!in_array('author_id', $fields, true)) {
                    $fields[] = 'author_id';
                }
            }

            if ($lastEditor) {
                if (!in_array('editor_id', $fields, true)) {
                    $fields[] = 'editor_id';
                }
            }
        }
        $slave = $this->model->getSlaveDbConnection();
        $sql = $slave->select()->from($this->model->table(), $fields);

        if ($filters) {
            $this->queryAddFilters($sql, $filters);
        }

        if ($query && strlen($query)) {
            $this->queryAddQuery($sql, $query);
        }

        if ($params) {
            $this->queryAddPagerParams($sql, $params);
        }

        if (is_array($joins) && !empty($joins)) {
            $this->queryAddJoins($sql, $joins);
        }

        $data = $slave->fetchAll($sql);

        if (!$author && !$lastEditor) {
            return $data;
        }

        $ids = array();

        foreach ($data as $row) {
            if ($author) {
                $ids[] = $row['author_id'];
            }

            if ($lastEditor) {
                $ids[] = $row['editor_id'];
            }
        }

        if (!empty($ids)) {
            array_unique($ids);
            $usersData = Model::factory('User')->query()->filters(['id' => $ids])->fields(['id', 'name'])->fetchAll();
            if (!empty($usersData)) {
                $usersData = Utils::rekey('id', $usersData);
            }
        }

        foreach ($data as &$row) {
            if ($author) {
                if (isset($usersData[$row['author_id']])) {
                    $row[$author] = $usersData[$row['author_id']]['name'];
                } else {
                    $row[$author] = '';
                }
            }

            if ($lastEditor) {
                if (isset($usersData[$row['editor_id']])) {
                    $row[$lastEditor] = $usersData[$row['editor_id']]['name'];
                } else {
                    $row[$lastEditor] = '';
                }
            }
        }
        return $data;
    }

    /**
     * Get a list of records
     * @param array|boolean $params - optional parameters array('start'=>0,'limit'=>10,'sort'=>'fieldname','dir'=>'DESC')
     * @param array|boolean $filters - optional filters (where) the key - the field name, value
     * @param array|string $fields - optional  list of fields to retrieve
     * @param boolean $useCache - use hard cache
     * @param string|boolean $query - optional string for search (since 0.9)
     * it is necessary to remember that hard cache gets invalidated only at the end of its life cycle (configs / main.php),
     * is used in case update triggers can’t be applied
     * @param array|boolean $joins - optional, inclusion config for Zend_Select:
     * array(
     *          array(
     *                'joinType'=> joinLeft/left, joinRight/right, joinInner/inner
     *                'table' => array / string
     *                'fields => array / string
     *                'condition'=> string
     *          )...
     * )
     * @return array
     * @deprecated
     */
    public function getList($params = false, $filters = false, $fields = ['*'], $useCache = false, $query = false, $joins = false)
    {
        $data = false;
        $cache = $this->model->getCacheAdapter();
        $slave = $this->model->getSlaveDbConnection();
        $cacheKey = '';

        if ($useCache && $cache) {
            $cacheKey = $this->model->getCacheKey(array('list', serialize(func_get_args())));
            $data = $cache->load($cacheKey);
        }

        if ($data === false) {

            $queryObject = new Model\Query($this->model);
            $queryObject->fields($fields);

            if (is_array($filters) && !empty($filters)) {
                $queryObject->filters($filters);
            }

            if ($params) {
                $queryObject->params($params);
            }

            if ($query && strlen($query)) {
                $queryObject->search($query);
            }

            if (is_array($joins) && !empty($joins)) {
                $queryObject->joins($joins);
            }

            $data = $slave->fetchAll($queryObject->sql());

            if ($useCache && $cache) {
                $cache->save($data, $cacheKey, $this->model->getCacheTime());
            }
        }
        return $data;
    }

    /**
     * Get a number of objects (rows in a table)
     * @param array|bool $filters — optional - filters (where) the key - the field name, value
     * @param string|bool $query - optional - search query — search query
     * @param boolean $useCache — use hard cache
     * it is necessary to remember that hard cache gets invalidated only at the end of its life cycle (configs / main.php),
     * is used in case update triggers can’t be applied
     * @return int
     * @deprecated
     */
    public function getCount($filters = false, $query = false, $useCache = false)
    {
        $cParams = '';
        $cacheKey = '';
        $data = false;

        $cache = $this->model->getCacheAdapter();

        if ($useCache && $cache) {
            if ($filters) {
                $cParams .= serialize($filters);
            }

            if ($query) {
                $cParams .= $query;
            }

            $cacheKey = $this->model->getCacheKey(array('count', $cParams));
            $data = $cache->load($cacheKey);
        }

        if ($data === false) {
            if (empty($filters)) {
                $filters = null;
            }

            if (empty($query)) {
                $query = null;
            }

            $data = $this->model->query()->filters($filters)->search($query)->getCount();

            if ($useCache && $cache) {
                $cache->save($data, $cacheKey, $this->model->getCacheTime());
            }

        }
        return $data;
    }

    /**
     * Add filters (where) to the query
     * @param Db\Select $sql
     * @param array $filters the key - the field name, value
     * @return void
     * @deprecated
     */
    public function queryAddFilters(Db\Select $sql, $filters): void
    {
        if (!is_array($filters) || empty($filters)) {
            return;
        }
        $query = new Model\Query($this->model);
        $query->applyFilters($sql, $filters);
    }

    /**
     * Add author selection join to the query.
     * Used with rev_control objects
     * @param Db\Select $sql
     * @param string $fieldAlias
     * @return void
     * @deprecated
     */
    protected function queryAddAuthor(Db\Select $sql, string $fieldAlias): void
    {
        $sql->joinLeft(array('u1' => Model::factory('User')->table()), 'author_id = u1.id',
            array($fieldAlias => 'u1.name'));
    }

    /**
     * Add editor selection join to the query.
     * Used with rev_control objects
     * @param Db\Select $sql
     * @param string $fieldAlias
     * @return void
     * @deprecated
     */
    protected function queryAddEditor(Db\Select $sql, $fieldAlias): void
    {
        $sql->joinLeft(array('u2' => Model::factory('User')->table()), 'editor_id = u2.id',
            array($fieldAlias => 'u2.name'));
    }

    /**
     * Add pagination parameters to a query
     * Used in CRUD-controllers for list pagination and sorting
     * @param Db\Select $sql
     * @param array $params — possible keys: start,limit,sort,dir
     * @return void
     * @deprecated
     */
    public function queryAddPagerParams(Db\Select $sql, $params): void
    {
        if (!is_array($params) || empty($params)) {
            return;
        }
        $query = new Model\Query($this->model);
        $query->applyParams($sql, $params);
    }

    /**
     * Get object by unique field
     * @param string $fieldName
     * @param string $value
     * @param mixed $fields - optional
     * @throws \Exception
     * @return array
     * @deprecated
     */
    public function getItemByUniqueField(string $fieldName, $value, $fields = '*')
    {
        return $this->model->getItemByField($fieldName, $value, $fields);
    }
}