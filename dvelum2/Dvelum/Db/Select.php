<?php
/*
 * DVelum project http://code.google.com/p/dvelum/ , http://dvelum.net
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
 *
 */
declare(strict_types=1);

namespace Dvelum\Db;
/**
 * Class for building SQL SELECT queries
 * Represents an implementation of the Zend_Db_select interface,
 * features simplified logic and better performance.
 * Functionality is practically identical to that of Zend_Db_Select, so it is easy to use for those, who is familiar with the latter.
 * introduced in DVelum 0.9
 */
class Select
{
    const JOIN_INNER = 'inner';
    const JOIN_LEFT = 'left';
    const JOIN_RIGHT = 'right';

    public $localCache = true;

    /**
     * @var Adapter $dbAdapter
     */
    protected $dbAdapter = false;

    protected $distinct = false;

    protected $from,
        $where,
        $join,
        $group,
        $having,
        $limit,
        $order,
        $orWhere,
        $orHaving,
        $forUpdate;

    protected $assembleOrder = [
        '_getDistinct' => 'distinct',
        '_getFrom' => 'from',
        '_getJoins' => 'join',
        '_getWhere' => 'where',
        'getOrWhere' => 'orWhere',
        'getGroup' => 'group',
        'getHaving' => 'having',
        'getOrHaving' => 'orHaving',
        'getOrder' => 'order',
        'getLimit' => 'limit',
        'getForUpdate' => 'forUpdate'

    ];

    protected $aliasCount = [];

    /**
     * @param Adapter $adapter
     */
    public function setDbAdapter(Adapter $adapter){
        $this->dbAdapter = $adapter;
    }

    /**
     * Add a DISTINCT clause
     * @return self
     */
    public function distinct()
    {
        $this->distinct = true;
        return $this;
    }

    /**
     * Add a FROM clause to the query
     * @param mixed $table string table name or array('alias'=>'tablename')
     * @param mixed $columns
     * @return self
     */
    public function from($table, $columns = "*") : self
    {
        if (!is_array($columns)) {
            if ($columns !== '*')
                $columns = $this->convertColumnsString($columns);
            else
                $columns = [$columns];
        }

        $this->from = ['table' => $table, 'columns' => $columns];
        return $this;
    }

    /**
     * Set columns
     * @param array $columns
     * @return self
     */
    public function columns(array $columns) : self{
        $this->from['columns'] = $columns;
        return $this;
    }

    /**
     * Add a WHERE clause
     * @param string $condition
     * @param mixed $bind
     * @return self
     */
    public function where($condition, $bind = false)  : self
    {
        if (!is_array($this->where))
            $this->where = array();

        $this->where[] = array('condition' => $condition, 'bind' => $bind);
        return $this;
    }

    /**
     * Add a OR WHERE clause to the query
     * @param string $condition
     * @param mixed $bind
     * @return self
     */
    public function orWhere($condition, $bind = false) : self
    {
        if (!is_array($this->orWhere))
            $this->orWhere = array();

        $this->orWhere[] = array('condition' => $condition, 'bind' => $bind);

        return $this;
    }

    /**
     * Add a GROUP clause to the query
     * @param mixed $fields string field name or array of field names
     * @return self
     */
    public function group($fields) : self
    {
        if (!is_array($this->group))
            $this->group = array();

        if (!is_array($fields))
            $fields = explode(',', $fields);

        foreach ($fields as $field)
            $this->group[] = $field;

        return $this;
    }

    /**
     * Add a HAVING clause to the query
     * @param string $condition
     * @param mixed $bind
     * @return self
     */
    public function having($condition, $bind = false) : self
    {
        if (!is_array($this->having))
            $this->having = array();

        $this->having[] = array('condition' => $condition, 'bind' => $bind);

        return $this;
    }

    /**
     * Add a OR HAVING clause to the query
     * @param string $condition
     * @param mixed $bind
     * @return self
     */
    public function orHaving($condition, $bind = false) : self
    {
        if (!is_array($this->orHaving))
            $this->orHaving = array();

        $this->orHaving[] = array('condition' => $condition, 'bind' => $bind);

        return $this;
    }

    /**
     * Adding another table to the query using JOIN
     * @param string $table
     * @param mixed $cond
     * @param mixed $cols
     * @param string $type
     * @return self
     */
    public function join($table, $cond, $cols = '*', string $type ='inner') : self
    {
        $this->addJoin($table, $cond, $cols, $type);

        return $this;
    }

    /**
     * Adding another table to the query using INNER JOIN
     * @param mixed $table
     * @param mixed $cond
     * @param mixed $cols
     * @deprecated
     * @return self
     */
    public function joinInner($table, $cond, $cols = '*') : self
    {
        $this->addJoin( $table, $cond, $cols, self::JOIN_INNER);

        return $this;
    }

    /**
     * Adding another table to the query using LEFT JOIN
     * @param mixed $table
     * @param mixed $cond
     * @param mixed $cols
     * @deprecated
     * @return self
     */
    public function joinLeft($table, $cond, $cols = '*') : self
    {
        $this->addJoin($table, $cond, $cols, self::JOIN_LEFT);

        return $this;
    }

    /**
     * Adding another table to the query using RIGHT JOIN
     * @param mixed $table
     * @param mixed $cond
     * @param mixed $cols
     * @deprecated
     * @return self
     */
    public function joinRight($table, $cond, $cols = '*') : self
    {
        $this->addJoin($table, $cond, $cols, self::JOIN_RIGHT);

        return $this;
    }

    /**
     * @param $table
     * @param $cond
     * @param $cols
     * @param string $type
     * @return Select
     */
    protected function addJoin($table, $cond, $cols, string $type) : self
    {
        if (!is_array($table) || is_int(key($table))) {
            if (is_array($table))
                $table = $table[key($table)];

            if (!isset($this->aliasCount[$table]))
                $this->aliasCount[$table] = 0;

            $tableAlias = $table;

            if ($this->aliasCount[$table])
                $tableAlias = $table . '_' . $this->aliasCount[$table];

            $this->aliasCount[$table]++;

            $table = array($tableAlias => $table);
        } else {
            $key = key($table);
            $table = array($key => $table[$key]);
        }

        if (!is_array($cols)) {
            if ($cols !== '*')
                $cols = $this->convertColumnsString($cols);
            else
                $cols = array($cols);
        }

        if (!is_array($this->join))
            $this->join = array();

        $this->join[] = array('type' => $type, 'table' => $table, 'condition' => $cond, 'columns' => $cols);

        return $this;
    }

    /**
     * Adding a LIMIT clause to the query
     * @param int $count
     * @param mixed $offset - optional
     * @return self
     */
    public function limit(int $count, $offset = false) : self
    {
        $this->limit = ['count' => $count, 'offset' => $offset];

        return $this;
    }

    /**
     * Adding offset
     * @param $offset
     * @return self
     */
    public function offset($offset) : self
    {
        $this->limit['offset'] = $offset;
        return $this;
    }

    /**
     * Setting the limit and count by page number.
     * @param int $page Limit results to this page number.
     * @param int $rowCount Use this many rows per page.
     * @return self
     */
    public function limitPage($page, $rowCount) : self
    {
        $page = ($page > 0) ? $page : 1;
        $rowCount = ($rowCount > 0) ? $rowCount : 1;
        $this->limit = array('count' => (int)$rowCount, 'offset' => (int)$rowCount * ($page - 1));
        return $this;
    }

    /**
     * Adding an ORDER clause to the query
     * @param mixed $spec
     * @param boolean $asIs optional
     * @return self
     */
    public function order($spec, $asIs = false) : self
    {
        if ($asIs) {
            $this->order = array($spec);
            return $this;
        }

        $result = array();
        if (!is_array($spec)) {
            $items = explode(',', $spec);
            foreach ($items as $str) {
                $str = trim($str);
                $wArray = explode(' ', $str);
                $wArray[0] = $this->quoteIdentifier($wArray[0]);
                $result[] = implode(' ', $wArray);
            }
        } else {
            foreach ($spec as $key => $type) {
                if (is_int($key)) {
                    if (strpos(trim($type), ' '))
                        $result[] = $type;
                    else
                        $result[] = $this->quoteIdentifier($type);
                } else {
                    $result[] = $this->quoteIdentifier($key) . ' ' . strtoupper($type);
                }
            }
        }
        $this->order = $result;
        return $this;
    }

    /**
     * Makes the query SELECT FOR UPDATE.
     *
     * @param bool $flag Whether or not the SELECT is FOR UPDATE (default true).
     * @return self
     */
    public function forUpdate($flag = true) : self
    {
        $this->forUpdate = $flag;
        return $this;
    }

    public function __toString() : string
    {
        return $this->assemble();
    }

    public function assemble() : string
    {
        $sql = 'SELECT ';
        foreach ($this->assembleOrder as $method => $data)
            if (!empty($this->$data))
                $sql = $this->$method($sql);
        return $sql . ';';
    }


    protected function _getDistinct($sql) : string
    {
        if ($this->distinct)
            $sql .= 'DISTINCT ';

        return $sql;
    }

    protected function _getFrom($sql) : string
    {
        $columns = $this->tableFieldsList($this->from['table'], $this->from['columns']);
        $tables = array();

        $tables[] = $this->tableAlias($this->from['table']);

        if (!empty($this->join))
            foreach ($this->join as $config)
                $columns = array_merge($columns, $this->tableFieldsList($config['table'], $config['columns']));

        $sql .= implode(', ', $columns) . ' FROM ' . implode(', ', $tables);

        return $sql;
    }

    protected function _getJoins($sql)
    {
        foreach ($this->join as $item)
            $sql .= $this->compileJoin($item);

        return $sql;
    }

    protected function compileJoin(array $config) : string
    {
        $str = '';
        //type, table , condition
        switch ($config['type']) {
            case self::JOIN_INNER :
                $str .= ' INNER JOIN ';
                break;
            case self::JOIN_LEFT :
                $str .= ' LEFT JOIN ';
                break;
            case self::JOIN_RIGHT :
                $str .= ' RIGHT JOIN ';
                break;
        }

        $str .= $this->tableAlias($config['table']) . ' ON ' . $config['condition'];
        return $str;
    }

    protected function _getWhere($sql) : string
    {
        $where = $this->prepareWhere($this->where);

        return $sql . ' WHERE (' . implode(' AND ', $where) . ')';
    }

    protected function prepareWhere($list) : array
    {
        $where = [];

        foreach ($list as $item) {
            if ($item['bind'] === false) {
                $where[] = $item['condition'];
            } else {
                $items = [];
                if (is_array($item['bind'])) {
                    $list = [];

                    foreach ($item['bind'] as $listValue)
                        $list[] = $this->quote($listValue);

                    $item['bind'] = implode(',', $list);
                } else {
                    $item['bind'] = $this->quote($item['bind']);
                }

                $where[] = str_replace('?', $item['bind'], $item['condition']);
            }
        }
        return $where;
    }

    protected function getOrWhere($sql) : string
    {
        $where = $this->prepareWhere($this->orWhere);
        return $sql . ' OR (' . implode(' ) OR ( ', $where) . ')';
    }

    protected function getHaving($sql) : string
    {
        $having = $this->prepareWhere($this->having);
        return $sql . ' HAVING (' . implode(' AND ', $having) . ')';
    }

    protected function getOrHaving($sql) : string
    {
        $having = $this->prepareWhere($this->orHaving);
        return $sql . ' OR (' . implode(' ) OR ( ', $having) . ')';
    }

    protected function getGroup($sql) : string
    {
        foreach ($this->group as &$item)
            $item = $this->quoteIdentifier($item);

        return $sql . ' GROUP BY ' . implode(',', $this->group);
    }

    protected function getOrder($sql) : string
    {
        return $sql . ' ORDER BY ' . implode(',', $this->order);
    }

    protected function getLimit($sql) : string
    {
        if ($this->limit['offset'])
            return $sql . ' LIMIT ' . intval($this->limit['offset']) . ',' . $this->limit['count'];
        else
            return $sql . ' LIMIT ' . $this->limit['count'];
    }

    protected function getForUpdate($sql) : string
    {
        if ($this->forUpdate) {
            return $sql . ' FOR UPDATE';
        } else {
            return $sql;
        }
    }

    /**
     * Quote a string as an identifier
     * @param string $str
     * @return string
     */
    public function quoteIdentifier($str) : string
    {
        return '`' . str_replace(array('`', '.'), array('', '`.`'), $str) . '`';
    }

    /**
     * Quote a raw string.
     * @param string $value Raw string
     * @return string Quoted string
     */
    protected function quote($value) : string
    {
        if (is_int($value)) {
            return (string) $value;
        } elseif (is_float($value)) {
            return sprintf('%F', $value);
        }

        if($this->dbAdapter){
            return $this->dbAdapter->quote((string)$value);
        }else{
            trigger_error(
                'Attempting to quote a value in ' . get_class($this) .
                ' without extension/driver support can introduce security vulnerabilities in a production environment'
            );
            return '\'' . addcslashes((string) $value, "\x00\n\r\\'\"\x1a") . '\'';
        }
    }

    protected function tableAlias($table) : string
    {
        static $cache = [];

        $data = '';

        // performance patch
        if ($this->localCache) {
            if (is_array($table))
                $hash = md5(serialize($table));
            else
                $hash = $table;

            if (isset($cache[$hash]))
                return $cache[$hash];
        }

        if (!is_array($table)) {
            $data = $this->quoteIdentifier($table);
        } else {
            $key = key($table);

            if (is_int($key))
                $data = $this->quoteIdentifier($table[$key]);
            else
                $data = $this->quoteIdentifier($table[$key]) . ' AS ' . $this->quoteIdentifier($key);
        }

        if ($this->localCache)
            $cache[$hash] = $data;

        return $data;
    }

    /**
     * @return array
     */
    protected function tableFieldsList($table, array $columns) : array
    {
        static $cache = [];

        // performance patch
        if ($this->localCache) {
            $hash = md5(serialize(func_get_args()));
            if (isset($cache[$hash]))
                return $cache[$hash];
        }

        $result = [];

        if (is_array($table)) {
            $key = key($table);

            if (is_int($key))
                $table = $table[$key];

            $table = $key;
        }

        foreach ($columns as $k => $v) {
            $wordsCount = str_word_count($v, 0, "_*\"");

            if (is_int($k)) {

                if (!strlen($v))
                    continue;

                if ($v === '*') {
                    $result[] = $this->quoteIdentifier($table) . '.*';
                } else {
                    if ($wordsCount === 1)
                        $result[] = $this->quoteIdentifier($table . '.' . $v);
                    else
                        $result[] = $v;
                }
            } else {
                if (!strlen($v) || !strlen($k))
                    continue;

                if ($wordsCount === 1)
                    $v = $this->quoteIdentifier($table . '.' . $v);

                $result[] = $v . ' AS ' . $this->quoteIdentifier($k);
            }
        }

        if ($this->localCache)
            $cache[$hash] = $result;

        return $result;
    }

    protected function convertColumnsString($str)
    {
        $items = explode(',', $str);
        return array_map('trim', $items);
    }
}