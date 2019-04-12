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

namespace Dvelum\Tree;

class Item implements \ArrayAccess
{
    /**
     * @var int|string
     */
    protected $id;
    /**
     * @var int|string
     */
    protected $parent;
    /**
     * @var array
     */
    protected $data;
    /**
     * @var int|null
     */
    protected $order;

    /**
     * Item constructor.
     * @param string|int $id
     * @param string|int $parent
     * @param mixed $data
     * @param int|null $order
     */
    public function __construct($id = null, $parent = null, $data = null, ?int $order = null)
    {
        $this->id = $id;
        $this->parent = $parent;
        $this->data = $data;
        $this->order = $order;
    }

    /**
     * @return int|string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int|string $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return int|string
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param int|string $parent
     */
    public function setParent($parent): void
    {
        $this->parent = $parent;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data): void
    {
        $this->data = $data;
    }

    /**
     * @return int|null
     */
    public function getOrder(): ?int
    {
        return $this->order;
    }

    /**
     * @param int|null $order
     */
    public function setOrder(?int $order): void
    {
        $this->order = $order;
    }

    public function offsetExists($offset): bool
    {
        return isset($this->$offset);
    }

    public function offsetGet($offset)
    {
        return $this->$offset;
    }

    public function offsetSet($offset, $value)
    {
        $this->$offset = $value;
    }

    public function offsetUnset($offset)
    {
        $this->$offset = null;
    }

    public function __toArray()
    {
        return [
            'id' => $this->id,
            'parent' => $this->parent,
            'data' => $this->data,
            'order' => $this->order
        ];
    }
}