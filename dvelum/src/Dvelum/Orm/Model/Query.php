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

namespace Dvelum\Orm\Model;

use Dvelum\Db\Adapter;
use Dvelum\Db;
use Dvelum\Db\Select\Filter;
use Dvelum\Orm\Model;
use Dvelum\Orm\Stat;

class Query
{
    const SEARCH_TYPE_STARTS_WITH = 'starts';
    const SEARCH_TYPE_CONTAINS = 'contains';
    const SEARCH_TYPE_ENDS_WITH = 'ends';
    /**
     * @var Model $model
     */
    protected $model;
    /**
     * @var Adapter $db
     */
    protected $db;

    protected $search = null;
    protected $searchType = self::SEARCH_TYPE_CONTAINS;
    protected $filters = null;
    protected $params = null;
    protected $fields = ['*'];
    protected $joins = null;
    protected $table = null;
    protected $tableAlias = null;

    public function __construct(Model $model)
    {
        $this->table = $model->table();
        $this->model = $model;
        $this->db = $model->getDbConnection();
    }

    /**
     * Change database connection
     * @param Adapter $connection
     * @return Query
     */
    public function setDbConnection(Adapter $connection) : self
    {
        $this->db = $connection;
        return $this;
    }

    /**
     * @param string $table
     * @return Query
     */
    public function table(string $table): Query
    {
        $this->table = $table;
        return $this;
    }

    /**
     * @param string $alias
     * @return Query
     */
    public function tableAlias(?string $alias): Query
    {
        $this->tableAlias = $alias;
        return $this;
    }

    /**
     * @param array|null $filters
     * @return Query
     */
    public function filters(?array $filters): Query
    {
        $this->filters = $filters;
        return $this;
    }

    /**
     * @param string $query
     * @param string $queryType
     * @return Query
     */
    public function search(?string $query, ?string $queryType = self::SEARCH_TYPE_CONTAINS): Query
    {
        $this->search = $query;
        $this->searchType = $queryType;
        return $this;
    }

    /**
     * @param array|null $params
     * @return Query
     */
    public function params(?array $params): Query
    {
        $this->params = $params;
        return $this;
    }

    /**
     * @param mixed $fields
     * @return Query
     */
    public function fields($fields): Query
    {
        $this->fields = $fields;
        return $this;
    }

    /**
     * @param array|null $joins
     * Config Example:
     * array(
     *        array(
     *            'joinType'=>   jonLeft/left , jonRight/right , joinInner/inner
     *            'table' => array / string
     *            'fields => array / string
     *            'condition'=> string
     *        )...
     * )
     * @return Query
     */
    public function joins(?array $joins): Query
    {
        $this->joins = $joins;
        return $this;
    }


    /**
     * Apply query filters
     * @param Db\Select $sql
     * @param array $filters
     * @return void
     */
    public function applyFilters(Db\Select $sql, array $filters): void
    {
        $filters = $this->clearFilters($filters);

        foreach ($filters as $k => $v) {
            if ($v instanceof Filter) {
                $v->applyTo($this->db, $sql);
            } else {
                if (is_array($v) && !empty($v)) {
                    $sql->where($this->db->quoteIdentifier($k) . ' IN(?)', $v);
                } elseif (is_bool($v)) {
                    $sql->where($this->db->quoteIdentifier($k) . ' = ' . intval($v));
                } elseif ((is_string($v) && strlen($v)) || is_numeric($v)) {
                    $sql->where($this->db->quoteIdentifier($k) . ' =?', $v);
                } elseif (is_null($v)) {
                    $sql->where($this->db->quoteIdentifier($k) . ' IS NULL');
                }
            }
        }
    }

    /**
     * Apply Search
     * @param Db\Select $sql
     * @param string $query
     * @param string $queryType
     * @return void
     */
    public function applySearch(Db\Select $sql, $query, string $queryType): void
    {
        $searchFields = $this->model->getSearchFields();

        if (!empty($searchFields)) {
            if (empty($this->tableAlias)) {
                $alias = $this->table;
            } else {
                $alias = $this->tableAlias;
            }

            $q = [];

            foreach ($searchFields as $v) {
                switch ($queryType) {
                    case self::SEARCH_TYPE_CONTAINS:
                        $q[] = $alias . "." . $v . " LIKE(" . $this->db->quote('%' . $query . '%') . ")";
                        break;
                    case self::SEARCH_TYPE_STARTS_WITH:
                        $q[] = $alias . "." . $v . " LIKE(" . $this->db->quote($query . '%') . ")";
                        break;
                    case self::SEARCH_TYPE_ENDS_WITH:
                        $q[] = $alias . "." . $v . " LIKE(" . $this->db->quote('%' . $query) . ")";
                        break;
                }

            }
            $sql->where('(' . implode(' OR ', $q) . ')');
        }
    }

    /**
     * Apply query params (sorting and pagination)
     * @param Db\Select $sql
     * @param array $params
     */
    public function applyParams($sql, array $params): void
    {
        if (isset($params['limit'])) {
            $sql->limit(intval($params['limit']));
        }

        if (isset($params['start'])) {
            $sql->offset(intval($params['start']));
        }

        if (!empty($params['sort']) && !empty($params['dir'])) {
            if (is_array($params['sort']) && !is_array($params['dir'])) {
                $sort = [];
                foreach ($params['sort'] as $key => $field) {
                    if (!is_int($key)) {
                        $order = trim(strtolower($field));
                        if ($order == 'asc' || $order == 'desc') {
                            $sort[$key] = $order;
                        }
                    } else {
                        $sort[$field] = $params['dir'];
                    }
                }
                $sql->order($sort);
            } else {
                $sql->order([(string)$params['sort'] => $params['dir']]);
            }
        }
    }

    /**
     * Apply Join conditions
     * @param Db\Select $sql
     * @param array $joins
     */
    public function applyJoins($sql, array $joins)
    {
        foreach ($joins as $config) {
            switch ($config['joinType']) {

                case 'joinLeft' :
                case 'left':
                    $sql->join($config['table'], $config['condition'], $config['fields'], Db\Select::JOIN_LEFT);
                    break;
                case 'joinRight' :
                case 'right':
                    $sql->join($config['table'], $config['condition'], $config['fields'], Db\Select::JOIN_RIGHT);
                    break;
                case 'joinInner':
                case 'inner':
                    $sql->join($config['table'], $config['condition'], $config['fields'], Db\Select::JOIN_INNER);
                    break;
            }
        }
    }

    /**
     * Prepare filter values , clean empty filters
     * @param array $filters
     * @return array
     */
    public function clearFilters(array $filters): array
    {
        $fields = $this->model->getLightConfig()->get('fields');
        foreach ($filters as $field => $val) {

            if ($val === false && isset($fields[$field]) && isset($fields[$field]['db_type']) && $fields[$field]['db_type'] === 'boolean') {
                $filters[$field] = \Dvelum\Filter::filterValue(\Dvelum\Filter::FILTER_BOOLEAN, $val);
                continue;
            }

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
     * Prepare Db\Select object
     * @return Db\Select
     */
    public function sql(): Db\Select
    {
        $sql = $this->db->select();

        if (!empty($this->tableAlias)) {
            $sql->from([$this->tableAlias => $this->table]);
        } else {
            $sql->from($this->table);
        }

        $sql->columns($this->fields);

        if (!empty($this->filters)) {
            $this->applyFilters($sql, $this->filters);
        }

        if (!empty($this->search)) {
            $this->applySearch($sql, $this->search, $this->searchType);
        }

        if (!empty($this->params)) {
            $this->applyParams($sql, $this->params);
        }

        if (!empty($this->joins)) {
            $this->applyJoins($sql, $this->joins);
        }

        return $sql;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->sql()->__toString();
    }

    /**
     * Fetch all records
     * @return array
     * @throws \Exception
     */
    public function fetchAll(): array
    {
        try {
            return $this->db->fetchAll($this->__toString());
        } catch (\Exception $e) {
            $this->model->logError($e->getMessage());
            throw $e;
        }
    }

    /**
     * Fetch one
     * @return mixed
     * @throws \Exception
     */
    public function fetchOne()
    {
        try {
            return $this->db->fetchOne($this->__toString());
        } catch (\Exception $e) {
            $this->model->logError($e->getMessage());
            throw $e;
        }
    }

    /**
     * Fetch first result row
     * @return array
     * @throws \Exception
     */
    public function fetchRow(): array
    {
        try {
            $result = $this->db->fetchRow($this->__toString());
            if (empty($result)) {
                $result = [];
            }
            return $result;
        } catch (\Exception $e) {
            $this->model->logError($e->getMessage());
            throw $e;
        }
    }

    /**
     * Fetch column
     * @return array
     * @throws \Exception
     */
    public function fetchCol(): array
    {
        try {
            return $this->db->fetchCol($this->__toString());
        } catch (\Exception $e) {
            $this->model->logError($e->getMessage());
            throw $e;
        }
    }

    /**
     * Count the number of rows that satisfy the filters
     * @param bool $approximateValue - Get approximate count for innodb table (only for queries without filters)
     * @return int
     * @throws \Exception
     */
    public function getCount(bool $approximateValue = false): int
    {
        $joins = $this->joins;
        $filters = $this->filters;
        $query = $this->search;
        $searchType = $this->searchType;
        $tableAlias = $this->tableAlias;

        // disable fields selection
        if (!empty($joins)) {
            foreach ($joins as & $config) {
                $config['fields'] = [];
            }
            unset($config);
        }
        $count = 0;

        if($approximateValue && empty($filters) && empty($query)) {
            $stat = new Stat();
            $config = $this->model->getObjectConfig();
            $data = $stat->getDetails($config->getName(), $this->db);
            if(!empty($data) && isset($data[0]) && isset($data[0]['records'])){
                $count = (int) str_replace(' ', '', $data[0]['records']);
            }
        }

        // get exact count
        if($count < 10000)
        {
            $sqlQuery = new Model\Query($this->model);
            $sqlQuery->setDbConnection($this->db);
            $sqlQuery->fields(['count' => 'COUNT(*)'])->tableAlias($tableAlias)
                ->filters($filters)->search($query, $searchType)
                ->joins($joins);

            if(!empty($this->tableAlias)){
                $sqlQuery->tableAlias((string) $this->tableAlias);
            }

            $count = $sqlQuery->fetchOne();
        }


        if (empty($count)) {
            $count = 0;
        }
        return (int)$count;
    }
}