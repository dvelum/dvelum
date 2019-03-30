<?php
/**
 * DVelum project http://code.google.com/p/dvelum/ , https://github.com/k-samuel/dvelum , http://dvelum.net
 * Copyright (C) 2011-2017  Kirill Yegorov
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

namespace Dvelum\App\Module\Generator;

use Dvelum\Lang;
use Dvelum\Orm;
use Dvelum\Request;
use Dvelum\Utils;
use Dvelum\View;

class Simple extends AbstractAdapter
{
    /**
     * Postfix for Object Link title fields
     * @var string
     */
    protected $linkedFieldPostfix = '_title';

    /**
     * List of Designer editors which require individual tab
     * @var array
     */
    public $tabTypes = [
        'Component_Field_System_Medialibhtml',
        'Component_Field_System_Related',
        'Component_Field_System_Objectslist'
    ];


    /**
     * Export array code
     * @param array $array
     * @return string
     */
    public function exportArrayToString(array $array): string
    {
        $elements = [];

        foreach ($array as $index => $item) {
            if (is_numeric($index)) {
                $elements[] = var_export($item, true);
            } else {
                $elements[] = "'" . $index . "' => " . var_export($item, true);
            }
        }
        return '[' . implode(',', $elements) . ']';
    }

    public function createModule(string $object, string $controllerClass, string $projectFile)
    {
        $lang = Lang::lang();

        //prepare class name
        $objectName = Utils\Strings::formatClassName($object);

        $jsName = str_replace('_', '', $objectName);
        $runNamespace = 'app' . $jsName . 'Application';
        $classNamespace = 'app' . $jsName . 'Components';

        $objectConfig = Orm\Record\Config::factory($object);
        $primaryKey = $objectConfig->getPrimaryKey();

        $objectFieldsConfig = $objectConfig->getFieldsConfig(false);

        $objectFields = [];
        $searchFields = [];
        $linkedObjects = [];

        /*
         * Skip text fields
        */
        foreach ($objectFieldsConfig as $key => $item) {
            $fieldItem = $objectConfig->getField($key);

            if ($fieldItem->isObjectLink() || $fieldItem->isMultiLink()) {
                $linkedObjects[] = $fieldItem->getLinkedObject();
            }

            if (in_array($item['db_type'], Orm\Record\Builder::$textTypes, true)) {
                continue;
            }

            if (isset($item['hidden']) && $item['hidden']) {
                continue;
            }

            $objectFields[] = $key;
            if (isset($item['is_search']) && $item['is_search']) {
                $searchFields[] = $key;
            }
        }

        $dataFields = array();
        foreach ($objectConfig->getFieldsConfig(true) as $key => $item) {
            if (in_array($item['db_type'], Orm\Record\Builder::$textTypes,
                    true) || $objectConfig->getField($key)->isMultiLink()) {
                continue;
            }

            if (isset($item['hidden']) && $item['hidden']) {
                continue;
            }

            $dataFields[] = $key;
        }

        array_unshift($objectFields, $primaryKey);
        $linksToShow = [];
        $links = array_keys($objectConfig->getLinks(
            [
                Orm\Record\Config::LINK_OBJECT,
                Orm\Record\Config::LINK_OBJECT_LIST,
                // Db_Object_Config::LINK_DICTIONARY  (dictionary renderer by default)
            ],
            false
        ));

        $additionalModelFields = [];

        foreach ($links as $k => $v) {
            if (!$objectConfig->isSystemField($v)) {
                $linksToShow[$v . $this->linkedFieldPostfix] = $v;
                $objectField = $objectConfig->getField($v);
                if ($objectField->isObjectLink() || $objectField->isMultiLink()) {
                    $additionalModelFields[$v] = $v . $this->linkedFieldPostfix;
                }
            }
        }
        $linkToField = array_flip($linksToShow);

        $template = View::factory();
        $controllerNamespace = substr($controllerClass, 0,  strrpos($controllerClass ,'\\'));
        $template->setData([
            'controller_namespace' => $controllerNamespace,
            'listFields' => $this->exportArrayToString($dataFields),
            'listLinks' => $this->exportArrayToString($linksToShow),
            'canViewObjects' => $this->exportArrayToString($linkedObjects),
            'moduleName' => $objectName,
            'objectName' => $objectName
        ]);

        $controllerCode = '<?php ' . PHP_EOL . $template->render('generator/simple.php');
        /*
         * Create controller
         */
        $controllerDir = $this->appConfig->get('local_controllers') . str_replace('\\','/', $controllerNamespace);
        $this->createControllerFile($controllerDir, $controllerCode);

        /*
         * Designer project
        */
        $project = new \Designer_Project();
        $project->namespace = $classNamespace;
        $project->runnamespace = $runNamespace;

        /*
         * Project events
        */
        $eventManager = $project->getEventManager();

        $storeFields = \Backend_Designer_Import::checkImportORMFields($object, $dataFields);

        foreach ($storeFields as $itemObject) {
            /**
             * @var \Ext_Object $object
             */
            $name = $itemObject->name;
            if (isset($linkToField[$name])) {
                $storeFields[] = \Ext_Factory::object('Data_Field', array(
                    'name' => $linkToField[$name],
                    'type' => 'string'
                ));
            }
        }
        // Add fields for linked lists
        if (!empty($additionalModelFields)) {
            foreach ($additionalModelFields as $dataIndex) {
                $storeFields[] = \Ext_Factory::object('Data_Field', array(
                    'name' => $dataIndex,
                    'type' => 'string'
                ));
            }
        }

        $urlTemplates = $this->designerConfig->get('templates');

        $controllerUrl = $this->request->url([$urlTemplates['adminpath'], $object, '']);
        $storeUrl = $this->request->url([$urlTemplates['adminpath'], $object, 'list']);

        $modelName = str_replace('_', '', $objectName) . 'Model';
        /**
         * @var \Ext_Model $model
         */
        $model = \Ext_Factory::object('Model');
        $model->setName($modelName);
        $model->idProperty = $primaryKey;
        $model->addFields($storeFields);

        $project->addObject(\Designer_Project::COMPONENT_ROOT, $model);


        $dataStore = \Ext_Factory::object('Data_Store');
        $dataStore->setName('dataStore');
        $dataStore->autoLoad = true;
        $dataStore->model = $modelName;

        $dataReader = \Ext_Factory::object('Data_Reader_Json', [
            'rootProperty' => 'data',
            'totalProperty' => 'count',
            'type' => 'json'
        ]);

        $dataProxy = \Ext_Factory::object('Data_Proxy_Ajax', [
            'type' => 'ajax',
            'reader' => $dataReader,
            'url' => $storeUrl,
            'startParam' => 'pager[start]',
            'limitParam' => 'pager[limit]',
            'sortParam' => 'pager[sort]',
            'directionParam' => 'pager[dir]',
            'simpleSortMode' => true,
        ]);

        $dataStore->proxy = $dataProxy;
        $dataStore->remoteSort = true;

        $project->addObject(\Designer_Project::LAYOUT_ROOT, $dataStore);

        /**
         * Data grid
         * @var \Ext_Grid $dataGrid
         */
        $dataGrid = \Ext_Factory::object('Grid',[
            'store' => 'dataStore',
            'columnLines' => true,
            'title' => $objectConfig->getTitle() . ' :: ' . $lang->get('HOME'),
            'viewConfig' => '{enableTextSelection: true}',
            'minHeight' => 400
        ]);
        
        $dataGrid->setName('dataGrid');
        $dataGrid->setAdvancedProperty('paging', true);
       
        $dataGrid->extendedComponent(true);

        $this->addGridMethods($project, $dataGrid, $object, false);

        $eventManager->setEvent('dataGrid', 'itemdblclick', 'this.showEditWindow(record.get("id"));');

        $objectFieldList = \Backend_Designer_Import::checkImportORMFields($object, $objectFields);

        if (!empty($objectFieldList)) {
            /**
             * @var \Ext_Grid_Column_Action $column
             */
            $column = \Ext_Factory::object('Grid_Column_Action',[
                'text'=>'',
                'align'=>'center',
                'width'=>40
            ]);
            $column->setName($dataGrid->getName() . '_pre_actions');

            $editButton = \Ext_Factory::object('Grid_Column_Action_Button',[
                'text' => '',
                'icon' => '[%wroot%]i/system/edit.png',
                'tooltip' => '[js:] appLang.EDIT_ITEM',
                'isDisabled' => 'function(){return !this.canEdit;}',
            ]);
            $editButton->setName($dataGrid->getName() . '_actions_edit');

            $eventManager->setEvent($editButton->getName(), 'handler',
                'this.showEditWindow(grid.getStore().getAt(rowIndex).get("id"));');

            $column->addAction($editButton->getName(), $editButton);
            $dataGrid->addColumn($column->getName(), $column, $parent = 0);

            foreach ($objectFieldList as $fieldConfig) {
                // skip object link column (will be set with $additionalModelFields)
                if ($objectConfig->getField($fieldConfig->name)->isObjectLink()) {
                    continue;
                }

                switch ($fieldConfig->type) {
                    case 'boolean':
                        $column = \Ext_Factory::object('Grid_Column_Boolean');
                        $column->renderer = 'Ext_Component_Renderer_System_Checkbox';
                        $column->width = 50;
                        $column->align = 'center';
                        break;
                    case 'integer':
                        $column = \Ext_Factory::object('Grid_Column');
                        break;

                    case 'float':
                        $column = \Ext_Factory::object('Grid_Column_Number');
                        if (isset($objectFieldsConfig[$fieldConfig->name]['db_precision'])) {
                            $column->format = '0,000.' . str_repeat('0',
                                    $objectFieldsConfig[$fieldConfig->name]['db_precision']);
                        }
                        break;
                    case 'date':
                        $column = \Ext_Factory::object('Grid_Column_Date');
                        if ($objectFieldsConfig[$fieldConfig->name]['db_type'] == 'time') {
                            $column->format = 'H:i:s';
                        }
                        break;
                    default:
                        $column = \Ext_Factory::object('Grid_Column');
                }

                if ($objectConfig->fieldExists($fieldConfig->name)) {
                    $cfg = $objectConfig->getFieldConfig($fieldConfig->name);
                    $column->text = $cfg['title'];
                } else {
                    $column->text = $fieldConfig->name;
                }

                $column->dataIndex = $fieldConfig->name;
                $column->setName($fieldConfig->name);
                $column->itemId = $column->getName();

                $itemField = $objectConfig->getField($fieldConfig->name);
                if ($itemField->isDictionaryLink()) {
                    $dictionary = $itemField->getLinkedDictionary();
                    $rendererHelper = new \Ext_Helper_Grid_Column_Renderer();
                    $rendererHelper->setType(\Ext_Helper_Grid_Column_Renderer::TYPE_DICTIONARY);
                    $rendererHelper->setValue($dictionary);
                    $column->renderer = $rendererHelper;
                }

                $dataGrid->addColumn($column->getName(), $column, $parent = 0);
            }

            // Add fields for linked lists
            if (!empty($additionalModelFields)) {
                foreach ($additionalModelFields as $field => $dataIndex) {
                    $cfg = $objectConfig->getFieldConfig($field);
                    $column = \Ext_Factory::object('Grid_Column',[
                        'dataIndex'=> $dataIndex,
                        'sortable' => false,
                        'itemId' => $dataIndex,
                        'text' => $cfg['title']
                    ]);
                    $column->setName($dataIndex);
                    $dataGrid->addColumn($column->getName(), $column, $parent = 0);
                }
            }
            /**
             * @var \Ext_Grid_Column_Action $column
             */
            $column = \Ext_Factory::object('Grid_Column_Action',[
                'text' => '[js:] appLang.ACTIONS',
                'align' => 'center',
                'width' => 50
            ]);
            $column->setName($dataGrid->getName() . '_actions');

            $deleteButton = \Ext_Factory::object('Grid_Column_Action_Button',[
                'text'=> 'dg_action_delete',
                'icon'=> '[%wroot%]i/system/delete.png',
                'tooltip' => '[js:] appLang.DELETE',
                'isDisabled' => 'function(){return !this.canDelete;}'
            ]);
            $deleteButton->setName($dataGrid->getName() . '_actions_delete');

            $eventManager->setEvent(
                $deleteButton->getName(), 
                'handler',
                'this.deleteRecord(grid.getStore().getAt(rowIndex));'
            );

            $column->addAction($deleteButton->getName(), $deleteButton);
            $dataGrid->addColumn($column->getName(), $column, $parent = 0);
        }
        $project->addObject(\Designer_Project::COMPONENT_ROOT, $dataGrid);


        /**
         * Instance of data grid to layout
         * @var \Ext_Object_Instance $gridInstance
         */
        $gridInstance = \Ext_Factory::object('Object_Instance');
        $gridInstance->setObject($dataGrid);
        $gridInstance->setName($dataGrid->getName());
        $project->getTree()->addItem(
            $gridInstance->getName() . '_instance', 
            \Designer_Project::LAYOUT_ROOT,
            $gridInstance
        );

        /*
         * Top toolbar
        */
        $dockObject = \Ext_Factory::object('Docked');
        $dockObject->setName($dataGrid->getName() . '__docked');
        $project->addObject($dataGrid->getName(), $dockObject);

        $filters = \Ext_Factory::object('Toolbar');
        $filters->setName('filters');
        $project->addObject($dockObject->getName(), $filters);

        /*
         * Top toolbar items
        */

        $addButton = \Ext_Factory::object('Button');
        $addButton->setName('addButton');
        $addButton->text = $lang->get('ADD_ITEM');
        $addButton->icon = '[%wroot%]i/system/add_icon.png';

        $eventManager->setEvent('addButton', 'click', 'this.showEditWindow(false);');

        $project->addObject($filters->getName(), $addButton);

        $sep1 = \Ext_Factory::object('Toolbar_Separator');
        $sep1->setName('sep1');
        $project->addObject($filters->getName(), $sep1);

        if (!empty($searchFields)) {
            $searchField = \Ext_Factory::object('Component_Field_System_Searchfield');
            $searchField->setName('searchField');
            $searchField->width = 200;
            $searchField->store = $dataStore->getName();
            $searchField->fieldNames = json_encode($searchFields);

            $fill = \Ext_Factory::object('Toolbar_Fill');
            $fill->setName('fill1');
            $project->addObject($filters->getName(), $fill);
            $project->addObject($filters->getName(), $searchField);
        }

        /*
         * Editor window
        */
        $editWindow = \Ext_Factory::object('Component_Window_System_Crud',[
            'objectName' => $object,
            'controllerUrl' => $controllerUrl,
            'width' => 800,
            'height' => 650,
            'modal' => true,
            'resizable' => true,
            'showToolbar' => false,
        ]);
        $editWindow->setName('editWindow');
        $editWindow->extendedComponent(true);
        
        if (!$objectConfig->hasHistory()) {
            $editWindow->hideEastPanel = true;
        }

        $project->addObject(\Designer_Project::COMPONENT_ROOT, $editWindow);

        $tab = \Ext_Factory::object('Panel',[
            'frame' => false,
            'border' => false,
            'layout' => 'anchor',
            'bodyPadding' => 3,
            'bodyCls' => 'formBody',
            'anchor' => '100%',
            'title' => $lang->get('GENERAL'),
            'scrollable' => true,
            'fieldDefaults' => "{
                                    labelAlign: 'right',
                                    labelWidth: 160,
                                    anchor: '100%'
                                }"
        ]);
        $tab->setName($editWindow->getName() . '_generalTab');
        

        $project->addObject($editWindow->getName(), $tab);

        $objectFieldList = array_keys($objectConfig->getFieldsConfig(false));

        foreach ($objectFieldList as $field) {
            if ($field == $primaryKey) {
                continue;
            }

            $fieldConfig = $objectConfig->getFieldConfig($field);

            if (isset($fieldConfig['hidden']) && $fieldConfig['hidden']) {
                continue;
            }

            $newField = \Backend_Designer_Import::convertOrmFieldToExtField($field, $fieldConfig);

            if ($newField === false) {
                continue;
            }

            $newField->setName($editWindow->getName() . '_' . $field);
            $fieldClass = $newField->getClass();

            if ($fieldClass == 'Component_Field_System_Objectslist' || $fieldClass == 'Component_Field_System_Objectlink') {
                $newField->controllerUrl = $controllerUrl;
            }

            if (in_array($fieldClass, $this->tabTypes, true)) {
                $project->addObject($editWindow->getName(), $newField);
            } else {
                $project->addObject($tab->getName(), $newField);
            }

        }

        /*
         * Save designer project
        */
        $designerStorage = \Designer_Factory::getStorage($this->designerConfig);

        /*
         * Create ActionJS code
         */
        $project->setActionJs($this->createActionJS($object, $runNamespace, $classNamespace));

        if (!$designerStorage->save($projectFile, $project, $this->designerConfig->get('vcs_support'))) {
            throw new \Exception('Can`t create Designer project');
        }

        return true;
    }

    public function createVcModule(string $object, string $controllerClass, string $projectFile)
    {
        $lang = Lang::lang();

        //prepare class name
        $objectName = Utils\Strings::formatClassName($object);

        $jsName = str_replace('_', '', $objectName);
        $runNamespace = 'app' . $jsName . 'Application';
        $classNamespace = 'app' . $jsName . 'Components';

        $objectConfig = Orm\Record\Config::factory($object);
        $primaryKey = $objectConfig->getPrimaryKey();

        $objectFieldsConfig = $objectConfig->getFieldsConfig(false);
        $objectFields = array();
        $searchFields = array();
        $linkedObjects = array();

        /*
         * Skip text fields
        */
        foreach($objectFieldsConfig as $key => $item)
        {
            if($objectConfig->getField($key)->isObjectLink() || $objectConfig->getField($key)->isMultiLink()){
                $linkedObjects[] = $objectConfig->getField($key)->getLinkedObject();
            }

            if(in_array($item['db_type'] , Orm\Record\Builder::$textTypes , true) || $objectConfig->getField($key)->isObjectLink() || $objectConfig->getField($key)->isMultiLink())
                continue;

            if(isset($item['hidden']) && $item['hidden'])
                continue;


            $objectFields[] = $key;
            if(isset($item['is_search']) && $item['is_search'])
                $searchFields[] = $key;
        }

        $dataFields = array();
        foreach($objectConfig->getFieldsConfig(true) as $key => $item)
        {
            if(in_array($item['db_type'] , Orm\Record\Builder::$textTypes , true))
                continue;

            if(isset($item['hidden']) && $item['hidden'])
                continue;


            $dataFields[] = $key;
        }

        array_unshift($objectFields , $objectConfig->getPrimaryKey());

        $linksToShow = array_keys($objectConfig->getLinks(
            [
                Orm\Record\Config::LINK_OBJECT,
                Orm\Record\Config::LINK_OBJECT_LIST,
                // Db_Object_Config::LINK_DICTIONARY  (dictionary renderer by default)
            ],
            false
        ));

        foreach($linksToShow as $k=>$v){
            if($objectConfig->getField($v)->isSystem()){
                unset($linksToShow[$v]);
            }
        }

        $template = View::factory();
        $controllerNamespace =  $controllerNamespace = substr($controllerClass, 0,  strrpos($controllerClass ,'\\'));
        $template->setData([
            'controller_namespace' => $controllerNamespace,
            'listFields' => $this->exportArrayToString($dataFields),
            'listLinks' => $this->exportArrayToString($linksToShow),
            'canViewObjects' => $this->exportArrayToString($linkedObjects),
            'moduleName' => $objectName,
            'objectName' => $objectName
        ]);

        $controllerCode = '<?php ' . PHP_EOL . $template->render('generator/simple.php');
        /*
         * Create controller
        */
       // $acceptedDirs = $this->appConfig->get('backend_controllers_dirs');
        $controllerDir = $this->appConfig->get('local_controllers') . str_replace('\\','/', $controllerNamespace);
        $controllerFile = $this->createControllerFile($controllerDir, $controllerCode);

        /*
         * Designer project
        */
        $project = new \Designer_Project();
        $project->namespace = $classNamespace;
        $project->runnamespace = $runNamespace;

        /*
         * Project events
        */
        $eventManager = $project->getEventManager();

        /*
         * Data Storage
        */
        $storeFields = array(
            \Ext_Factory::object('Data_Field',array('name' => 'published','type' => 'boolean')),
            \Ext_Factory::object('Data_Field',array('name' => 'date_created','type' => 'date','dateFormat' =>'Y-m-d H:i:s')),
            \Ext_Factory::object('Data_Field',array('name' => 'date_updated','type' => 'date','dateFormat' =>'Y-m-d H:i:s')),
            \Ext_Factory::object('Data_Field',array('name' => 'date_published','type' => 'date','dateFormat' =>'Y-m-d H:i:s')),
            \Ext_Factory::object('Data_Field',array('name' => 'last_version','type' => 'integer')),
            \Ext_Factory::object('Data_Field',array('name' => 'published_version','type' => 'integer')),
            \Ext_Factory::object('Data_Field',array('name' => 'user','type' => 'string')),
            \Ext_Factory::object('Data_Field',array('name' => 'updater','type' => 'string')),
        );

        $storeFields = array_merge($storeFields , \Backend_Designer_Import::checkImportORMFields($object ,  $dataFields));

        $urlTemplates =  $this->designerConfig->get('templates');


        $controllerUrl = Request::factory()->url(array($urlTemplates['adminpath'] , $object , ''));
        $storeUrl = Request::factory()->url(array($urlTemplates['adminpath'] ,  $object , 'list'));

        $modelName = str_replace('_', '', $objectName) . 'Model';

        $model = \Ext_Factory::object('Model');
        $model->setName($modelName);
        $model->idProperty = $primaryKey;
        $model->addFields($storeFields);

        $project->addObject(\Designer_Project::COMPONENT_ROOT , $model);

        $dataStore = \Ext_Factory::object('Data_Store');
        $dataStore->setName('dataStore');
        $dataStore->model = $modelName;
        $dataStore->autoLoad = true;

        $dataProxy = \Ext_Factory::object('Data_Proxy_Ajax');
        $dataProxy->type = 'ajax';

        $dataReader = \Ext_Factory::object('Data_Reader_Json');
        $dataReader->rootProperty = 'data';
        $dataReader->totalProperty = 'count';
        $dataReader->type = 'json';

        $dataProxy->reader = $dataReader;
        $dataProxy->url = $storeUrl;

        $dataProxy->startParam = 'pager[start]';
        $dataProxy->limitParam = 'pager[limit]';
        $dataProxy->sortParam = 'pager[sort]';
        $dataProxy->directionParam = 'pager[dir]';
        $dataProxy->simpleSortMode = true;

        $dataStore->proxy = $dataProxy;
        $dataStore->remoteSort = true;

        $project->addObject(\Designer_Project::LAYOUT_ROOT  , $dataStore);

        /*
         * Data grid
        */
        $dataGrid = \Ext_Factory::object('Grid');
        $dataGrid->setName('dataGrid');
        $dataGrid->store = 'dataStore';
        $dataGrid->columnLines = true;
        $dataGrid->minHeight = 400;
        $dataGrid->title = $objectConfig->getTitle() . ' :: ' . $lang->HOME;
        $dataGrid->setAdvancedProperty('paging' , true);
        $dataGrid->viewConfig = '{enableTextSelection: true}';
        $dataGrid->extendedComponent(true);

        $this->addGridMethods($project , $dataGrid , $object , true);

        $eventManager->setEvent('dataGrid', 'itemdblclick', 'this.showEditWindow(record.get("id"));');

        $objectFieldList = \Backend_Designer_Import::checkImportORMFields($object , $objectFields);


        $publishedRec = new \stdClass();
        $publishedRec->name = 'published';
        $publishedRec->type = 'string';
        array_unshift($objectFieldList , $publishedRec);

        $createdRec = new \stdClass();
        $createdRec->name =  'date_created';
        $createdRec->type='';
        $objectFieldList[] = $createdRec;

        $updatedRec = new \stdClass();
        $updatedRec->name =  'date_updated';
        $updatedRec->type='';
        $objectFieldList[] = $updatedRec;

        foreach($objectFieldList as $fieldConfig)
        {

            switch($fieldConfig->type){
                case 'boolean':
                    $column = \Ext_Factory::object('Grid_Column_Boolean');
                    break;
                case 'integer':
                    $column = \Ext_Factory::object('Grid_Column');
                    break;
                case 'float':
                    $column = \Ext_Factory::object('Grid_Column_Number');
                    if(isset($objectFieldsConfig[$fieldConfig->name]['db_precision']))
                        $column->format = '0,000.'.str_repeat('0' , $objectFieldsConfig[$fieldConfig->name]['db_precision']);
                    break;
                case 'date':
                    $column = \Ext_Factory::object('Grid_Column_Date');
                    if($objectFieldsConfig[$fieldConfig->name]['db_type'] == 'time')
                        $column->format = 'H:i:s';
                    break;
                default:
                    $column = \Ext_Factory::object('Grid_Column');
            }

            if($objectConfig->fieldExists($fieldConfig->name)){
                $cfg = $objectConfig->getFieldConfig($fieldConfig->name);
                $column->text = $cfg['title'];
            }else{
                $column->text = $fieldConfig->name;
            }

            $column->dataIndex = $fieldConfig->name;
            $column->setName($fieldConfig->name);
            $column->itemId = $column->getName();

            switch($fieldConfig->name)
            {
                case $primaryKey:
                    $column->renderer = 'Ext_Component_Renderer_System_Version';
                    $column->text = '[js:] appLang.VERSIONS_HEADER';
                    $column->align = 'center';
                    $column->width = 147;
                    break;

                case 'published':
                    $column->renderer = 'Ext_Component_Renderer_System_Publish';
                    $column->text = '[js:] appLang.STATUS';
                    $column->align = 'center';
                    $column->width = 50;
                    break;

                case 'date_created':
                    $column->renderer = 'Ext_Component_Renderer_System_Creator';
                    $column->text = '[js:] appLang.CREATED_BY';
                    $column->align = 'center';
                    $column->width = 142;
                    break;

                case 'date_updated':
                    $column->renderer = 'Ext_Component_Renderer_System_Updater';
                    $column->text = '[js:] appLang.UPDATED_BY';
                    $column->align = 'center';
                    $column->width = 146;
                    break;
            }

            if($objectConfig->getField($fieldConfig->name)->isDictionaryLink()){
                $dictionary = $objectConfig->getField($fieldConfig->name)->getLinkedDictionary();
                $rendererHelper = new \Ext_Helper_Grid_Column_Renderer();
                $rendererHelper->setType(\Ext_Helper_Grid_Column_Renderer::TYPE_DICTIONARY);
                $rendererHelper->setValue($dictionary);
                $column->renderer = $rendererHelper;
            }

            $dataGrid->addColumn($column->getName() , $column , $parent = 0);
        }

        $column = \Ext_Factory::object('Grid_Column_Action');
        $column->text = '[js:] appLang.ACTIONS';
        $column->setName($dataGrid->getName().'_actions');
        $column->align = 'center';
        $column->width = 50;

        $deleteButton = \Ext_Factory::object('Grid_Column_Action_Button');
        $deleteButton->setName($dataGrid->getName().'_actions_delete');
        $deleteButton->text = 'dg_action_delete';
        $deleteButton->icon = '[%wroot%]i/system/delete.png';
        $deleteButton->tooltip = '[js:] appLang.DELETE';
        $deleteButton->isDisabled = 'function(){return !this.canDelete;}';

        $eventManager->setEvent($deleteButton->getName(), 'handler', 'this.deleteRecord(grid.getStore().getAt(rowIndex));');

        $column->addAction($deleteButton->getName() ,$deleteButton);
        $dataGrid->addColumn($column->getName() , $column , $parent = 0);

        $project->addObject(\Designer_Project::COMPONENT_ROOT, $dataGrid);

        /**
         * Instance of data grid to layout
         */
        $gridInstance = \Ext_Factory::object('Object_Instance');
        $gridInstance->setObject($dataGrid);
        $gridInstance->setName($dataGrid->getName());
        $project->getTree()->addItem($gridInstance->getName() . '_instance', \Designer_Project::LAYOUT_ROOT, $gridInstance);

        /*
         * Top toolbar
         */
        $dockObject = \Ext_Factory::object('Docked');
        $dockObject->setName($dataGrid->getName() . '__docked');
        $project->addObject($dataGrid->getName() , $dockObject);

        $filters = \Ext_Factory::object('Toolbar');
        $filters->setName('filters');
        $project->addObject($dockObject->getName() , $filters);

        /*
         * Top toolbar items
         */
        $addButton = \Ext_Factory::object('Button');
        $addButton->setName('addButton');
        $addButton->text = $lang->ADD_ITEM;
        $addButton->icon = '[%wroot%]i/system/add_icon.png';
        $eventManager->setEvent('addButton', 'click', 'this.showEditWindow(false);');

        $project->addObject($filters->getName() , $addButton);

        $sep1 = \Ext_Factory::object('Toolbar_Separator');
        $sep1->setName('sep1');
        $project->addObject($filters->getName() , $sep1);

        if(!empty($searchFields)){
            $searchField = \Ext_Factory::object('Component_Field_System_Searchfield');
            $searchField->setName('searchField');
            $searchField->width = 200;
            // $searchField->local = false;
            $searchField->store = $dataStore->getName();
            $searchField->fieldNames = json_encode($searchFields);

            $fill = \Ext_Factory::object('Toolbar_Fill');
            $fill->setName('fill1');
            $project->addObject($filters->getName() , $fill);
            $project->addObject($filters->getName() , $searchField);
        }

        /*
         * Editor window
        */
        $editWindow = \Ext_Factory::object('Component_Window_System_Crud_Vc');
        $editWindow->setName('editWindow');
        $editWindow->objectName = $object;
        $editWindow->controllerUrl = $controllerUrl;
        $editWindow->width = 800;
        $editWindow->height = 650;
        $editWindow->modal = true;
        $editWindow->resizable = true;
        $editWindow->extendedComponent(true);


        /*
         * Hide history panel
        */
        /*if(!$objectConfig->hasHistory())
         $editWindow->hideEastPanel = true;
        */

        $project->addObject(\Designer_Project::COMPONENT_ROOT, $editWindow);

        $tab = \Ext_Factory::object('Panel');
        $tab->setName($editWindow->getName() . '_generalTab');
        $tab->frame = false;
        $tab->border = false;
        $tab->layout = 'anchor';
        $tab->bodyPadding = 3;
        $tab->bodyCls = 'formBody';
        $tab->anchor = '100%';
        $tab->title = $lang->GENERAL;
        $tab->scrollable = true;
        $tab->fieldDefaults = "{labelAlign:'right', labelWidth: 160, anchor: '100%'}";

        $project->addObject($editWindow->getName() , $tab);

        $objectFieldList = array_keys($objectConfig->getFieldsConfig(false));

        foreach($objectFieldList as $field)
        {
            if($field == $primaryKey)
                continue;

            $fieldConfig = $objectConfig->getFieldConfig($field);

            if(isset($fieldConfig['hidden']) && $fieldConfig['hidden'])
                continue;

            $newField = \Backend_Designer_Import::convertOrmFieldToExtField($field , $fieldConfig);

            if($newField === false)
                continue;

            $newField->setName($editWindow->getName() . '_' . $field);
            $fieldClass = $newField->getClass();

            if($fieldClass == 'Component_Field_System_Objectslist' || $fieldClass == 'Component_Field_System_Objectlink')
                $newField->controllerUrl = $controllerUrl;

            if(in_array($fieldClass , $this->tabTypes , true))
                $project->addObject($editWindow->getName() , $newField);
            else
                $project->addObject($tab->getName() , $newField);
        }

        /*
         * Create ActionJS code
         */
        $project->setActionJs($this->createActionJS($object, $runNamespace, $classNamespace , true));

        /*
         * Save designer project
         */
        $designerStorage = \Designer_Factory::getStorage($this->designerConfig);

        if(!$designerStorage->save($projectFile , $project , $this->designerConfig->get('vcs_support'))){
            @unlink($controllerFile);
            throw new \Exception('Can`t create Designer project');
        }
        return true;
    }
}