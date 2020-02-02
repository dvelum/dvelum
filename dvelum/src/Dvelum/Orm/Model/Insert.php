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

use Dvelum\Orm\Model;
use Dvelum\Db\Adapter;

class Insert implements InsertInterface
{
    /**
     * @var Model $model
     */
    protected $model;
    /**
     * @var Adapter $db
     */
    protected $db;

    public function __construct(Model $model)
    {
        $this->model = $model;
        $this->db = $model->getDbConnection();
    }

    /**
     * Insert multiple rows (not safe but fast)
     * @param array $records
     * @param int $chunkSize, optional default 500
     * @param bool $ignore - optional default false Ignore errors
     * @return bool
     */
    public function bulkInsert(array $records, int $chunkSize = 500, bool $ignore = false) : bool
    {
        if (empty($records)) {
            return true;
        }

        $chunks = array_chunk($records, $chunkSize);

        $keys = array_keys($records[key($records)]);

        foreach ($keys as &$key) {
            $key = $this->db->quoteIdentifier((string)$key);
        }
        unset($key);

        $keys = implode(',', $keys);

        foreach ($chunks as $rowset) {
            foreach ($rowset as &$row) {
                foreach ($row as &$colValue) {
                    if (is_bool($colValue)) {
                        $colValue = intval($colValue);
                    } elseif (is_null($colValue)) {
                        $colValue = 'NULL';
                    } else {
                        $colValue = $this->db->quote($colValue);
                    }
                }
                unset($colValue);
                $row = implode(',', $row);
            }
            unset($row);

            $sql = 'INSERT ';

            if ($ignore) {
                $sql .= 'IGNORE ';
            }

            $sql .= 'INTO ' . $this->model->table() . ' (' . $keys . ') ' . "\n" . ' VALUES ' . "\n" . '(' . implode(')' . "\n" . ',(',
                    array_values($rowset)) . ') ' . "\n" . '';

            try {
                $this->db->query($sql);
            } catch (\Exception $e) {
                $this->model->logError('multiInsert: ' . $e->getMessage());
                return false;
            }
        }
        return true;
    }

    /**
     * Insert single record on duplicate key update
     * @param array $data
     * @return bool
     */
    public function onDuplicateKeyUpdate(array $data): bool
    {
        if (empty($data)) {
            return true;
        }

        $keys = array_keys($data);

        foreach ($keys as &$val) {
            $val = $this->db->quoteIdentifier($val);
        }
        unset($val);

        $values = array_values($data);
        foreach ($values as &$val) {
            if(is_bool($val)){
                $val = intval($val);
            }elseif (is_null($val)){
                $val = 'NULL';
            }else{
                $val = $this->db->quote($val);
            }
        }
        unset($val);

        $sql = 'INSERT INTO ' . $this->db->quoteIdentifier($this->model->table()) . ' (' . implode(',',
                $keys) . ') VALUES (' . implode(',', $values) . ') ON DUPLICATE KEY UPDATE ';

        $updates = [];
        foreach ($keys as $key) {
            $updates[] = $key . ' = VALUES(' . $key . ') ';
        }

        $sql .= implode(', ', $updates) . ';';

        try {
            $this->db->query($sql);
            return true;
        } catch (\Exception $e) {
            $this->model->logError($e->getMessage() . ' SQL: ' . $sql);
            return false;
        }
    }
}