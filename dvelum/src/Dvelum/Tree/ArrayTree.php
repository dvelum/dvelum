<?php
/**
 * Class optimized for fast work with tree structures.
 * Easily handles up to 25000-30000 sets of elements (less than 1 second to fill out)
 * DVelum project https://github.com/dvelum/dvelum , http://dvelum.net
 * Copyright (C) 2011  Kirill A Egorov
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
 */
declare(strict_types=1);

namespace Dvelum\Tree;

use \Exception as Exception;

/**
 * Class Tree
 */
class ArrayTree
{
    protected $items = [];
    protected $children = [];

    /**
     * Set elements sorting order by ID
     * @param mixed $id — element identifier
     * @param integer $order — sorting order
     * @return boolean
     */
    public function setItemOrder($id, $order)
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
    public function sortItems($parentId = false)
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
     * @return boolean
     */
    public function itemExists($id)
    {
        return isset($this->items[$id]);
    }

    /**
     * Get the number of elements in a tree
     * @return integer
     */
    public function getItemsCount()
    {
        return sizeof($this->items);
    }

    /**
     * Add a node to the tree
     * @param mixed $id — unique identifier
     * @param mixed $parent — parent node identifier
     * @param mixed $data — node data
     * @param boolean|integer $order - sorting order, not required
     * @return boolean —  successfully invoked
     */
    public function addItem($id, $parent, $data, $order = false)
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
     * @return boolean —  successfully invoked
     */
    public function updateItem($id, $data)
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
     * @return array - an array with keys ('id','parent','order','data')
     * @throws Exception
     */
    public function getItem($id)
    {
        if ($this->itemExists($id)) {
            return $this->items[$id];
        } else {
            throw new Exception('Item "' . $id . '" is not found');
        }
    }

    /**
     * Get node data by ID
     * @param mixed $id
     * @return mixed
     */
    public function getItemData($id)
    {
        $data = $this->getItem($id);
        return $data['data'];
    }

    /**
     * Check if the node has child elements
     * @param mixed $id — node identifier
     * @return bool
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
     * @param mixed id - parent node identifier
     * @return array - an array with keys ('id','parent','order','data')
     */
    public function getChildrenRecursive($id): array
    {
        $data = [];
        if ($this->hasChildren($id)) {
            $children = $this->getChildren($id);
            foreach ($children as $k => $v) {
                $data[] = $v['id'];
                $subChildren = $this->getChildrenRecursive($v['id']);
                if (!empty($subChildren)) {
                    $data = array_merge($data, $subChildren);
                }
            }
        }
        return $data;
    }

    /**
     * @param mixed $id
     * @return void
     */
    protected function sortChildren($id): void
    {
        if (!isset($this->children[$id]) || empty($this->children[$id])) {
            return;
        }

        $tmp = [];

        foreach ($this->children[$id] as $key => &$dat) {
            $tmp[$dat['id']] = $dat['order'];
        }
        unset($dat);

        $this->children[$id] = [];
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
     * @return array
     * @var mixed id
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
        if (!$this->itemExists($id)) {
            return;
        }

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
     * @param mixed $id — child node identifier
     * @return mixed |false
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
     * @return bool
     */
    public function removeItem($id): bool
    {
        if ($this->itemExists($id)) {
            $this->remove($id);
        }

        return true;
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
