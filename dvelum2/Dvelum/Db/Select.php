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
    const JOIN_INNER = 1;
    const JOIN_LEFT = 2;
    const JOIN_RIGHT = 3;

    public $localCache = true;

    /**
     * @var \Mysqli
     */
    protected $_mysqliConnection = false;

    protected $_distinct = false;

    protected $_from,
        $_where,
        $_join,
        $_group,
        $_having,
        $_limit,
        $_order,
        $_orWhere,
        $_orHaving,
        $_forUpdate;

    protected $_assembleOrder = [
        '_getDistinct' => '_distinct',
        '_getFrom' => '_from',
        '_getJoins' => '_join',
        '_getWhere' => '_where',
        '_getOrWhere' => '_orWhere',
        '_getGroup' => '_group',
        '_getHaving' => '_having',
        '_getOrHaving' => '_orHaving',
        '_getOrder' => '_order',
        '_getLimit' => '_limit',
        '_getForUpdate' => '_forUpdate'

    ];

    protected $_aliasCount = [];

    /**
     * @param \mysqli $connection
     */
    public function setMysqli(\mysqli $connection){
        $this->_mysqliConnection = $connection;
    }

    /**
     * Add a DISTINCT clause
     * @return self
     */
    public function distinct()
    {
        $this->_distinct = true;
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
                $columns = $this->_convertColumnsString($columns);
            else
                $columns = [$columns];
        }

        $this->_from = ['table' => $table, 'columns' => $columns];
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
        if (!is_array($this->_where))
            $this->_where = array();

        $this->_where[] = array('condition' => $condition, 'bind' => $bind);
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
        if (!is_array($this->_orWhere))
            $this->_orWhere = array();

        $this->_orWhere[] = array('condition' => $condition, 'bind' => $bind);

        return $this;
    }

    /**
     * Add a GROUP clause to the query
     * @param mixed $fields string field name or array of field names
     * @return self
     */
    public function group($fields) : self
    {
        if (!is_array($this->_group))
            $this->_group = array();

        if (!is_array($fields))
            $fields = explode(',', $fields);

        foreach ($fields as $field)
            $this->_group[] = $field;

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
        if (!is_array($this->_having))
            $this->_having = array();

        $this->_having[] = array('condition' => $condition, 'bind' => $bind);

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
        if (!is_array($this->_orHaving))
            $this->_orHaving = array();

        $this->_orHaving[] = array('condition' => $condition, 'bind' => $bind);

        return $this;
    }

    /**
     * Adding another table to the query using JOIN
     * @param string $table
     * @param mixed $cond
     * @param mixed $cols
     * @return self
     */
    public function join($table, $cond, $cols = '*') : self
    {
        $this->joinInner($table, $cond, $cols);

        return $this;
    }

    /**
     * Adding another table to the query using INNER JOIN
     * @param mixed $table
     * @param mixed $cond
     * @param mixed $cols
     * @return self
     */
    public function joinInner($table, $cond, $cols = '*') : self
    {
        $this->_addJoin(self::JOIN_INNER, $table, $cond, $cols);

        return $this;
    }

    /**
     * Adding another table to the query using LEFT JOIN
     * @param mixed $table
     * @param mixed $cond
     * @param mixed $cols
     * @return self
     */
    public function joinLeft($table, $cond, $cols = '*') : self
    {
        $this->_addJoin(self::JOIN_LEFT, $table, $cond, $cols);

        return $this;
    }

    /**
     * Adding another table to the query using RIGHT JOIN
     * @param mixed $table
     * @param mixed $cond
     * @param mixed $cols
     * @return self
     */
    public function joinRight($table, $cond, $cols = '*') : self
    {
        $this->_addJoin(self::JOIN_RIGHT, $table, $cond, $cols);

        return $this;
    }

    protected function _addJoin($type, $table, $cond, $cols) : self
    {
        if (!is_array($table) || is_int(key($table))) {
            if (is_array($table))
                $table = $table[key($table)];

            if (!isset($this->_aliasCount[$table]))
                $this->_aliasCount[$table] = 0;

            $tableAlias = $table;

            if ($this->_aliasCount[$table])
                $tableAlias = $table . '_' . $this->_aliasCount[$table];

            $this->_aliasCount[$table]++;

            $table = array($tableAlias => $table);
        } else {
            $key = key($table);
            $table = array($key => $table[$key]);
        }

        if (!is_array($cols)) {
            if ($cols !== '*')
                $cols = $this->_convertColumnsString($cols);
            else
                $cols = array($cols);
        }

        if (!is_array($this->_join))
            $this->_join = array();

        $this->_join[] = array('type' => $type, 'table' => $table, 'condition' => $cond, 'columns' => $cols);

        return $this;
    }

    /**
     * Adding a LIMIT clause to the query
     * @param integer $count
     * @param mixed $offset - optional
     * @return self
     */
    public function limit($count, $offset = false) : self
    {
        $this->_limit = ['count' => $count, 'offset' => $offset];

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
        $this->_limit = array('count' => (int)$rowCount, 'offset' => (int)$rowCount * ($page - 1));
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
            $this->_order = array($spec);
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
        $this->_order = $result;
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
        $this->_forUpdate = $flag;
        return $this;
    }

    public function __toString() : string
    {
        return $this->assemble();
    }

    public function assemble() : string
    {
        $sql = 'SELECT ';
        foreach ($this->_assembleOrder as $method => $data)
            if (!empty($this->$data))
                $sql = $this->$method($sql);
        return $sql . ';';
    }


    protected function _getDistinct($sql) : string
    {
        if ($this->_distinct)
            $sql .= 'DISTINCT ';

        return $sql;
    }

    protected function _getFrom($sql) : string
    {
        $columns = $this->_tableFieldsList($this->_from['table'], $this->_from['columns']);
        $tables = array();

        $tables[] = $this->_tableAlias($this->_from['table']);

        if (!empty($this->_join))
            foreach ($this->_join as $config)
                $columns = array_merge($columns, $this->_tableFieldsList($config['table'], $config['columns']));

        $sql .= implode(', ', $columns) . ' FROM ' . implode(', ', $tables);

        return $sql;
    }

    protected function _getJoins($sql)
    {
        foreach ($this->_join as $item)
            $sql .= $this->_compileJoin($item);

        return $sql;
    }

    protected function _compileJoin(array $config) : string
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

        $str .= $this->_tableAlias($config['table']) . ' ON ' . $config['condition'];
        return $str;
    }

    protected function _getWhere($sql) : string
    {
        $where = $this->_prepareWhere($this->_where);

        return $sql . ' WHERE (' . implode(' AND ', $where) . ')';
    }

    protected function _prepareWhere($list) : array
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
                        $list[] = $this->_quote($listValue);

                    $item['bind'] = implode(',', $list);
                } else {
                    $item['bind'] = $this->_quote($item['bind']);
                }

                $where[] = str_replace('?', $item['bind'], $item['condition']);
            }
        }
        return $where;
    }

    protected function _getOrWhere($sql) : string
    {
        $where = $this->_prepareWhere($this->_orWhere);
        return $sql . ' OR (' . implode(' ) OR ( ', $where) . ')';
    }

    protected function _getHaving($sql) : string
    {
        $having = $this->_prepareWhere($this->_having);
        return $sql . ' HAVING (' . implode(' AND ', $having) . ')';
    }

    protected function _getOrHaving($sql) : string
    {
        $having = $this->_prepareWhere($this->_orHaving);
        return $sql . ' OR (' . implode(' ) OR ( ', $having) . ')';
    }

    protected function _getGroup($sql) : string
    {
        foreach ($this->_group as &$item)
            $item = $this->quoteIdentifier($item);

        return $sql . ' GROUP BY ' . implode(',', $this->_group);
    }

    protected function _getOrder($sql) : string
    {
        return $sql . ' ORDER BY ' . implode(',', $this->_order);
    }

    protected function _getLimit($sql) : string
    {
        if ($this->_limit['offset'])
            return $sql . ' LIMIT ' . intval($this->_limit['offset']) . ',' . $this->_limit['count'];
        else
            return $sql . ' LIMIT ' . $this->_limit['count'];
    }

    protected function _getForUpdate($sql) : string
    {
        if ($this->_forUpdate) {
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
    protected function _quote($value) : string
    {
        if (is_int($value)) {
            return (string) $value;
        } elseif (is_float($value)) {
            return sprintf('%F', $value);
        }

        if($this->_mysqliConnection){
            return "'" . $this->_mysqliConnection->real_escape_string((string)$value) . "'";
        }else{
            return "'" . addcslashes($value, "\000\\'\"\032\n\r") . "'";
        }
    }

    protected function _tableAlias($table) : string
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
    protected function _tableFieldsList($table, array $columns) : array
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

    protected function _convertColumnsString($str)
    {
        $items = explode(',', $str);
        return array_map('trim', $items);
    }
}