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

interface InsertInterface
{
    /**
     * InsertInterface constructor.
     * @param Model $model
     */
    public function __construct(Model $model);

    /**
     * Insert multiple rows (not safe but fast)
     * @param array $records
     * @param int $chunkSize, optional default 500
     * @param bool $ignore - optional default false Ignore errors
     * @return bool
     */
    public function bulkInsert(array $records, int $chunkSize = 500, bool $ignore = false): bool;

    /**
     * Insert single record on duplicate key update
     * @param array $data
     * @return bool
     */
    public function onDuplicateKeyUpdate(array $data): bool;
}