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

namespace Dvelum\Db\Adapter;

class Event
{

    /**
     * @var int $code
     */
    protected $code;
    /**
     * @var array $data
     */
    protected $data;

    public function __construct(int $code, array $data = [])
    {
        $this->code = $code;
        $this->data = $data;
    }

    /**
     * Get event code
     * @return int
     */
    public function getCode() : int
    {
        return $this->code;
    }

    /**
     * Set event code
     * @param int $code
     */
    public function setCode(int $code): void
    {
        $this->code = $code;
    }

    /**
     * Get event data
     * @return array
     */
    public function getData() : array
    {
        return $this->data;
    }

    /**
     * Set event data
     * @param array $data
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }

}