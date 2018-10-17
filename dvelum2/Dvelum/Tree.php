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

namespace Dvelum;

use \Exception as Exception;

/**
 * Class optimized for fast work with tree structures.
 * Easily handles up to 25000-30000 sets of elements (less than 1 second to fill out)
 * Copyright (C) 2011  Kirill Yegorov
 * @package Dvelum
 */
class Tree
{
    protected $items = [];
    protected $children = [];

    /**
     * Set elements sorting order by ID
     * @param mixed $id — element identifier
     * @param integer $order — sorting order
     * @return bool
     */
    public function setItemOrder($id, int $order): bool
    {
        if (!$this->itemExists($id)) {
            return false;
        }

        $this->items[$id]['order'] = $order;
        return true;
    }

    /**
     * Sort child elements
     * @param mixed $parentId — nor required;  a parent identifier -
     * is the root node by default, which sorts all other nodes
     */
    public function sortItems($parentId = false): void
    {
        if ($parentId) {
            $this->sortChildren($parentId);
        } else {
            foreach ($this->children as $k => $v) {
                $this->sortChildren($k);
            }
        }
    }

    /**
     * Check if the node exists by its identifier
     * @param mixed $id
     * @return bool
     */
    public function itemExists($id): bool
    {
        return isset($this->items[$id]);
    }

    /**
     * Get the number of elements in a tree
     * @return int
     */
    public function getItemsCount(): int
    {
        return sizeof($this->items);
    }

    /**
     * Add a node to the tree
     * @param mixed $id — unique identifier
     * @param mixed $parent — parent node identifier
     * @param mixed $data — node data
     * @param bool|integer $order - sorting order, not required
     * @return bool —  successfully invoked
     */
    public function addItem($id, $parent, $data, $order = false): bool
    {
        if ($this->itemExists($id) || (string)$id === '0') {
            return false;
        }

        if ($order === false && isset($this->children[$parent])) {
            $order = sizeof($this->children[$parent]);
        }

        $this->items[$id] = [
            'id' => $id,
            'parent' => $parent,
            'data' => $data,
            'order' => $order
        ];

        if (!isset($this->children[$parent])) {
            $this->children[$parent] = [];
        }

        $this->children[$parent][$id] = &$this->items[$id];
        return true;
    }

    /**
     * Update the node data
     * @param mixed $id — node identifier
     * @param mixed $data — node data
     * @return bool —  successfully invoked
     */
    public function updateItem($id, $data): bool
    {
        if (!$this->itemExists($id) || (string)$id === '0') {
            return false;
        }

        $this->items[$id]['data'] = $data;
        return true;
    }

    /**
     * Get node structure by ID
     * @param mixed $id
     * @throws Exception
     * @return array - an array with keys ('id','parent','order','data')
     */
    public function getItem($id): array
    {
        if ($this->itemExists($id)) {
            return $this->items[$id];
        } else {
            throw new Exception('Item "' . $id . '" is not found');
        }
    }

    /**
     * Get node data by ID
     * @param string $id
     * @return mixed
     */
    public function getItemData($id)
    {
        $data = $this->getItem($id);
        return $data['data'];
    }

    /**
     * Check if the node has child elements
     * @param string $id — node identifier
     * @return boolean
     */
    public function hasChildren($id): bool
    {
        if (isset($this->children[$id]) && !empty($this->children[$id])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get data on all child elements (recursively)
     * @param mixed $id - parent node identifier
     * @return array - an array with keys ('id','parent','order','data')
     */
    public function getChildrenRecursive($id): array
    {
        $data = [];
        if($this->hasChildren($id))
        {
            $childs = $this->getChildren($id);
            foreach($childs as $k => $v)
            {
                $data[] = $v['id'];
                $subChilds = $this->getChildrenRecursive($v['id']);
                if(!empty($subChilds))
                    $data = array_merge($data , $subChilds);
            }
        }
        return $data;
    }

    protected function sortChildren($id): void
    {
        if (!isset($this->children[$id]) || empty($this->children[$id])) {
            return;
        }

        $tmp = array();

        foreach ($this->children[$id] as $key => &$dat) {
            $tmp[$dat['id']] = $dat['order'];
        }
        unset($dat);

        $this->children[$id] = array();
        asort($tmp);

        $sort = 0;
        foreach ($tmp as $key => $order) {
            $this->items[$key]['order'] = $sort;
            $this->children[$id][$this->items[$key]['id']] = &$this->items[$key];
            $sort++;
        }
    }

    /**
     * Get child nodes’ structures
     * @var mixed id
     * @return array
     */
    public function getChildren($id): array
    {
        if (!$this->hasChildren($id)) {
            return [];
        }

        return $this->children[$id];
    }

    /**
     * Recursively removing
     * @param mixed $id
     * @return void
     */
    protected function remove($id): void
    {
        $children = $this->getChildren($id);

        if (!empty($children)) {
            foreach ($children as $k => &$v) {
                $this->remove($v['id']);
            }
        }

        if (isset($this->children[$id])) {
            unset($this->children[$id]);
        }

        $parent = $this->items[$id]['parent'];

        if (!empty($this->children[$parent]) && isset($this->children[$parent][$id])) {
            unset($this->children[$parent][$id]);
        }

        unset($this->items[$id]);
    }

    /**
     * Get the parent node identifier by the child node identifier
     * @param string $id — child node identifier
     * @return mixed string or false
     */
    public function getParentId($id)
    {
        if (!$this->itemExists($id)) {
            return false;
        }

        $data = $this->getItem($id);
        return $data['parent'];
    }

    /**
     * Change the parent node for the node
     * @param mixed $id — node identifier
     * @param mixed $newParent — new parent node identifier
     * @return bool
     */
    public function changeParent($id, $newParent): bool
    {
        if (!$this->itemExists($id) || (!$this->itemExists($newParent) && (string)$newParent !== '0') || (string)$id == (string)$newParent) {
            return false;
        }

        $oldParent = $this->items[$id]['parent'];
        $this->items[$id]['parent'] = $newParent;

        if (!empty($this->children[$oldParent]) && isset($this->children[$oldParent][$id])) {
            unset($this->children[$oldParent][$id]);
        }

        $this->children[$newParent][$id] = &$this->items[$id];
        return true;
    }

    /**
     * Delete node
     * @param mixed $id
     * @return void
     */
    public function removeItem($id): void
    {
        if ($this->itemExists($id)) {
            $this->remove($id);
        }
    }

    /**
     * Get structures of the tree elements (nodes)
     * @return array - an array with keys ('id','parent','order','data')
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * Get list of parent nodes
     * @param mixed $id
     * @return array
     */
    public function getParentsList($id): array
    {
        $parents = [];
        if (!$this->itemExists($id)) {
            return [];
        }

        while ($this->getParentId($id)) {
            $p = $this->getParentId($id);
            $parents[] = $p;
            $id = $p;
        }

        if (!empty($parents)) {
            $parents = array_reverse($parents);
        }

        return $parents;
    }
}