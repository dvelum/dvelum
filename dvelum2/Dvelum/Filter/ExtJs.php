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

namespace Dvelum\Filter;

use Dvelum\Db\Select\Filter;

class ExtJs
{
    protected $operators = [
        'gt' => Filter::GT,
        'lt' => Filter::LT,
        'like' => Filter::LIKE,
        '=' => Filter::EQ,
        'eq' => Filter::EQ,
        'on' => Filter::EQ,
        'in' => Filter::IN,
        'ne' => Filter::NOT
    ];

    /**
     * Convert filters from ExtJs UI
     * into Db\Select\Filter
     * @param array $values
     * @return Filter[]
     */
    public function toDbSelect(array $values): array
    {
        $result = [];

        foreach ($values as $item)
        {
            if (!empty($item['operator'])) {
                $operator = $item['operator'];
            } else {
                $operator = $this->operators['eq'];
            }

            $value = $item['value'];
            $field = $item['property'];

            if (!isset($this->operators[$operator])) {
                continue;
            }

            if ($operator == 'like') {
                $result[] = new Filter($field, $value . '%', $this->operators[$operator]);
            } else {
                $result[] = new Filter($field, $value, $this->operators[$operator]);
            }
        }
        return $result;
    }
}