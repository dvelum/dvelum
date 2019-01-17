<?php
/*
 * DVelum project http://code.google.com/p/dvelum/ , http://dvelum.net
 * Copyright (C) 2011-2012  Kirill A Egorov
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

/**
 * Ext data grid implementation
 * @package Ext
 * @uses Lang
 */
class Ext_Grid extends Ext_Object
{
    /**
     * Columns tree
     * @var Tree
     */
    protected $_columns;
    /**
     * @var bool | Ext_Grid_Filtersfeature
     */
    protected $_filtersFeature = false;

    static public $advancedProperties = array(
        'checkboxSelection' => 'boolean',
        'clicksToEdit' => 'integer',
        'editable' => 'boolean',
        'enableGroupingMenu' => 'boolean',
        'expander_rowbodytpl' => 'raw',
        'groupsummary' => 'boolean',
        'hideGroupedHeader' => 'boolean',
        'numberedRows' => 'boolean',
        'paging' => 'boolean',
        'rowexpander' => 'boolean',
        'grouping' => 'boolean',
        'groupHeaderTpl' => 'string',
        'startCollapsed' => 'boolean',
        'summary' => 'boolean',
        'remoteRoot' => 'string'
    );

    protected $_advancedPropertyValues = array(
        'groupHeaderTpl' => "{name} ({rows.length})",
        'startCollapsed' => false,
        'clicksToEdit' => 2,
        'rowBodyTpl' => '',
        'enableGroupingMenu' => true,
        'hideGroupedHeader' => false,
        'expander_rowbodytpl' => ''
    );


    public function __construct()
    {
        parent::__construct();
        $this->_columns = new Tree();
    }

    /**
     * Add column
     * @param string $id
     * @param Ext_Object $object
     * @param string $parent , optional - paren object name
     * @return boolean
     */
    public function addColumn($id, $object, $parent = 0)
    {
        if (strpos($object->getClass(), 'Grid_Column') != 0)
            return false;

        return $this->_columns->addItem($id, $parent, $object);
    }

    /**
     * Set advanced property
     * @param string $key
     * @param mixed $value
     * @return boolean - success flag
     */
    public function setAdvancedProperty($key, $value)
    {
        if (!isset(self::$advancedProperties[$key])) {
            return false;
        }
        $this->_advancedPropertyValues[$key] = Filter::filterValue(self::$advancedProperties[$key], $value);
        return true;
    }

    /**
     * Get advanced properties
     * @return array
     */
    public function getAdvancedProperties()
    {
        return $this->_advancedPropertyValues;
    }

    /**
     * Check if column exists
     * @param string $id
     * @return boolean
     */
    public function columnExists($id)
    {
        return $this->_columns->itemExists($id);
    }

    /**
     * Remove column by id
     * @param string $id
     */
    public function removeColumn($id)
    {
        $this->_columns->removeItem($id);
    }

    /**
     * Updates column
     * @param string $id
     * @param mixed $data
     * @return boolean
     */
    public function updateColumn($id, $data)
    {
        return $this->_columns->updateItem($id, $data);
    }

    /**
     * Get column object by its ID
     * @param mixed $id
     * @throws Exception
     * @return Ext_Grid_Column
     */
    public function getColumn($id)
    {
        if (!$this->_columns->itemExists($id))
            throw new Exception('Invalid column ID');

        $item = $this->_columns->getItem($id);
        return $item['data'];
    }

    /**
     * Get columns data for TreePanel
     * @return array
     */
    public function getColumnsList()
    {
        return $this->_fillColumns($this->_columns);
    }

    /**
     * Get column list
     * @return array (tree containers)
     */
    public function getColumns()
    {
        return $this->_columns->getItems();
    }

    /**
     * Get column list as array (without tree architecture)
     * @return array
     */
    public function getColumnsArray()
    {
        $items = $this->_columns->getItems();
        $data = [];
        if (!empty($items))
            foreach ($items as $v)
                $data[$v['id']] = $v['data'];
        return $data;
    }

    /**
     * Get columns JS config
     * @return array
     */
    public function getColumnsConfig()
    {
        return $this->_compileColumns($this->_columns);
    }

    /**
     * Fill childs data array for columns tree
     * @param Tree $tree
     * @param mixed $root
     * @return array
     */
    protected function _fillColumns(Tree $tree, $root = 0)
    {
        $result = array();
        $childs = $tree->getChilds($root);

        if (empty($childs))
            return array();

        foreach ($childs as $v) {
            $object = $v['data'];

            $item = new stdClass();
            $item->id = $v['id'];
            $item->expanded = true;
            $item->leaf = false;
            $item->iconCls = 'objectIcon';
            $item->allowDrag = true;
            $item->text = $object->text;

            $item->children = [];

            if ($tree->hasChilds($v['id']))
                $item->children = $this->_fillColumns($tree, $v['id']);

            $result[] = $item;
        }
        return $result;
    }

    /**
     * Fill childs data array for columns tree
     * @param Tree $tree
     * @param mixed $root
     * @return array
     */
    protected function _compileColumns(Tree $tree, $root = 0)
    {
        $result = [];
        $childs = $tree->getChilds($root);

        if (empty($childs))
            return [];

        foreach ($childs as $v) {
            $column = $v['data'];
            $column->columns = '';
            if ($tree->hasChilds($v['id']))
                $column->columns = "[\n\t" . Utils_String::addIndent(implode(",\n", $this->_compileColumns($tree, $v['id'])), 2) . "\n]";

            $result[] = Utils_String::addIndent($column->__toString(), 2);
        }
        return $result;
    }

    /**
     * Changes parent of columns
     * @param string $name
     * @param string $newParent
     * @return void
     */
    public function changeParent($name, $newParent)
    {
        $this->_columns->changeParent($name, $newParent);
    }

    /**
     * Set item order
     * @param mixed $id
     * @param integer $order
     * @return boolean - success flag
     */
    public function setItemOrder($id, $order)
    {
        return $this->_columns->setItemOrder($id, $order);
    }

    /**
     * (non-PHPdoc)
     * @see Ext_Object::__toString()
     */
    public function __toString()
    {
        $this->_convertListeners();
        $lang = Lang::lang();
        $plugins = [];
        $features = [];

        if (isset($this->_advancedPropertyValues['checkboxSelection']) && $this->_advancedPropertyValues['checkboxSelection']) {
            $this->_config->selModel = 'Ext.create("Ext.selection.CheckboxModel")';
        }

        if (isset($this->_advancedPropertyValues['rowexpander']) && $this->_advancedPropertyValues['rowexpander']) {
            if (!empty($this->_advancedPropertyValues['expander_rowbodytpl']))
                $tpl = 'rowBodyTpl:' . $this->_advancedPropertyValues['expander_rowbodytpl'];
            else
                $tpl = '';

            $plugins[] = '{' . "\n" .
                "\t" . 'ptype: "rowexpander",' . "\n" .
                "\t" . 'pluginId:"rowexpander",' . "\n" .
                "\t" . $tpl . "\n" .
                '}';
        }

        if (isset($this->_advancedPropertyValues['editable']) && $this->_advancedPropertyValues['editable']) {
            $plugins[] = 'Ext.create("Ext.grid.plugin.CellEditing", {' . "\n" .
                "\t" . 'clicksToEdit: ' . $this->_advancedPropertyValues['clicksToEdit'] . ',' . "\n" .
                "\t" . 'pluginId:"cellediting"' . "\n" .
                '})';
        }


        if (isset($this->_advancedPropertyValues['grouping']) && $this->_advancedPropertyValues['grouping']) {


            if (isset($this->_advancedPropertyValues['groupsummary']) && $this->_advancedPropertyValues['groupsummary']) {
                $remoteRoot = '';

                if (isset($this->_advancedPropertyValues['remoteRoot']) && $this->_advancedPropertyValues['remoteRoot'])
                    $remoteRoot = 'remoteRoot: "' . $this->_advancedPropertyValues['remoteRoot'] . '"';


                $features[] = "{" .
                    "\t" . "id: '" . $this->getName() . "_groupingsummary'," . "\n" .
                    "\t" . "ftype: 'groupingsummary'," . "\n" .
                    "\t" . "groupHeaderTpl: '" . $this->_advancedPropertyValues['groupHeaderTpl'] . "'," . "\n" .
                    "\t" . "hideGroupedHeader:" . intval($this->_advancedPropertyValues['hideGroupedHeader']) . "," . "\n" .
                    "\t" . "startCollapsed: " . intval($this->_advancedPropertyValues['startCollapsed']) . "," . "\n" .
                    "\t" . "enableGroupingMenu: " . intval($this->_advancedPropertyValues['enableGroupingMenu']) . "," . "\n" .
                    "\t" . $remoteRoot . "\n" .
                    "}";
            } else {
                $features[] = "Ext.create('Ext.grid.feature.Grouping',{" . "\n" .
                    "\t" . "groupHeaderTpl: '" . $this->_advancedPropertyValues['groupHeaderTpl'] . "'," . "\n" .
                    "\t" . "startCollapsed: " . intval($this->_advancedPropertyValues['startCollapsed']) . "," . "\n" .
                    "\t" . "enableGroupingMenu: " . intval($this->_advancedPropertyValues['enableGroupingMenu']) . "," . "\n" .
                    "\t" . "hideGroupedHeader:" . intval($this->_advancedPropertyValues['hideGroupedHeader']) . "\n" .
                    "})";
            }
        }

        if ($this->hasFilters()) {
            $plugins[] = '{' . "\n" .
                "\t" . 'ptype: "gridfilters",' . "\n" .
                "\t" . 'pluginId:"gridfilters"' . "\n" .
                '}';
        }

        if (isset($this->_advancedPropertyValues['summary']) && $this->_advancedPropertyValues['summary']) {
            $features[] = '{id:"summary" , ftype: "summary"}';
        }

        $columns = '[]';
        $columnsList = $this->getColumnsConfig();

        if (!empty($columnsList)) {
            if (isset($this->_advancedPropertyValues['numberedRows']) && $this->_advancedPropertyValues['numberedRows'])
                $columns = "[\n\tExt.create('Ext.grid.RowNumberer'),\n" . implode(",\n\t", $columnsList) . "\n]";
            else
                $columns = "[\n" . implode(",\n\t", $columnsList) . "\n]";
        }

        unset($columnsList);

        if ($this->_config->isValidProperty('store') && strlen($this->_config->store)) {
            if (isset($this->_advancedPropertyValues['paging']) && $this->_advancedPropertyValues['paging']) {
                $this->_config->bbar = 'Ext.create("Ext.PagingToolbar", {' . "\n" .
                    // "\t"    .'store: '. $this->_config->store .','."\n".
                    "\t" . 'displayInfo: true,' . "\n" .
                    "\t" . 'displayMsg: "' . $lang->DISPLAYING_RECORDS . ' {0} - {1} ' . $lang->OF . ' {2}",' . "\n" .
                    "\t" . 'emptyMsg:appLang.NO_RECORDS_TO_DISPLAY,' . "\n" .
                    "\t" . "listeners:{ \n" .
                    "\t\t" . "beforerender:{\n" .
                    "\t\t\t" . "fn:function(cmp){\n" .
                    "\t\t\t\t" . "cmp.bindStore(cmp.up('grid').getStore());\n" .
                    "\t\t\t" . "}\n" .
                    "\t\t" . "}\n" .
                    "\t" . "}\n" .
                    "})";
            }
        }

        $this->_config->items = null;
        $this->_config->columns = $columns;

        if (!empty($plugins))
            $this->_config->plugins = '[' . "\n" . Utils_String::addIndent(implode(",\n", $plugins)) . "\n]";

        if (!empty($features))
            $this->_config->features = '[' . "\n" . Utils_String::addIndent(implode(",\n", $features)) . "\n]";

        return $this->_config->__toString();
    }

    /**
     * Sort Columns
     * @param array $data (column indexes)
     */
    public function updateColumnsSortingOrder(array $data)
    {
        $columns = $this->_columns->getItems();
        $count = count($columns);

        // reset sorting orders
        foreach ($columns as $id => $item) {
            $this->_columns->setItemOrder($id, $count);
        }

        foreach ($data as $orderNo => $colId) {
            if ($this->_columns->itemExists($colId)) {
                $this->_columns->setItemOrder($colId, $orderNo);
            }
        }
        $this->_columns->sortItems();
    }

    /**
     * Reindex columns (apply sort order)
     */
    public function reindexColumns()
    {
        $this->_columns->sortItems();
    }

    /**
     * Get Filtesr feature
     * @deprecated
     * @return Ext_Grid_Filtersfeature
     */
    public function getFiltersFeature()
    {
        if (!$this->_filtersFeature) {
            $this->_filtersFeature = Ext_Factory::object('Grid_Filtersfeature', array('id' => 'filters', 'paramPrefix' => 'filterfeature'));
        }

        return $this->_filtersFeature;
    }

    /**
     * Get object state for smart export
     */
    public function getState()
    {
        $state = parent::getState();
        $this->_columns->sortItems();
        $columns = $this->_columns->getItems();
        $colData = [];

        if (!empty($columns)) {
            $columns = Utils::sortByField($columns, 'order');

            foreach ($columns as $v) {
                $colData[$v['id']] = [
                    'id' => $v['id'],
                    'parent' => $v['parent'],
                    'class' => get_class($v['data']),
                    'name' => $v['data']->getName(),
                    'extClass' => $v['data']->getClass(),
                    'order' => $v['order'],
                    'state' => $v['data']->getState()
                ];
            }
        }

        $state['state'] = [
            '_advancedPropertyValues' => $this->_advancedPropertyValues,
        ];
        $state['columns'] = $colData;

        return $state;
    }

    /**
     * Set object state
     * @param $state
     */
    public function setState(array $state)
    {
        parent::setState($state);

        if (isset($state['columns']) && !empty($state['columns'])) {
            foreach ($state['columns'] as $v) {
                $col = Ext_Factory::object($v['extClass']);
                $col->setName($v['name']);
                $col->setState($v['state']);
                $this->_columns->addItem($v['id'], $v['parent'], $col, $v['order']);
            }
            $this->_columns->sortItems();
        }
    }

    /**
     * Check if columns has filters
     * @return boolean
     */
    public function hasFilters()
    {
        $columns = $this->_columns->getItems();
        foreach ($columns as $index => $data) {
            $col = $data['data'];
            if (!empty($col->filter) && $col->filter instanceof Ext_Grid_Filter) {
                return true;
            }
        }
        return false;
    }
}