<?php
/**
 *  DVelum project https://github.com/dvelum/dvelum
 *  Copyright (C) 2011-2018  Kirill Yegorov
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

namespace Dvelum\Orm\Distributed\Key\Strategy\VirtualBucket;

class IntToBucket implements MapperInterface
{
    private const BUCKET_SIZE = 2000;
    /**
     * Map key to bucket
     * @param mixed $key
     * @return Bucket
     */
    public function keyToBucket($key) : Bucket
    {
        $key = (int) $key;
        $index  = (int)($key / IntToBucket::BUCKET_SIZE);
        $id = $index + 1;
        $start = $index * IntToBucket::BUCKET_SIZE;
        $end = $start + IntToBucket::BUCKET_SIZE-1;
        $bucket = new Bucket();
        $bucket->setId($id);
        $bucket->setStart($start);
        $bucket->setEnd($end);
        return $bucket;
    }
}