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

namespace Dvelum\App\Backend\Menu;

use Dvelum\App\Backend;
use Dvelum\Orm\Record;
use Dvelum\Orm\Model;
use Dvelum\Orm\RecordInterface;
use Dvelum\App\Controller\EventManager;
use Dvelum\App\Controller\Event;
use \Exception;

class Controller extends Backend\Ui\Controller
{
    public function getModule(): string
    {
        return 'Menu';
    }
    public function getObjectName(): string
    {
        return 'Menu';
    }

    /**
     * (non-PHPdoc)
     * @see Backend_Controller_Crud::indexAction()
     */
    public function indexAction()
    {
        $module = $this->getModule();
        $this->resource->addInlineJs('
        	var canEdit = ' . ((int)$this->moduleAcl->canEdit($module)) . ';
        	var canDelete = ' . ((int)$this->moduleAcl->canDelete($module)) . ';
        	var menuItemlinkTypes = ' . \Dictionary::factory('link_type')->__toJs() . ';
        ');

        /**
         * @var \Model_Medialib $mediaModel
         */
        $mediaModel =  Model::factory('Medialib');
        $mediaModel->includeScripts($this->resource);

        $this->resource->addJs('/js/app/system/SearchPanel.js', 0);
        $this->resource->addJs('/js/app/system/HistoryPanel.js', 0);
        $this->resource->addJs('/js/app/system/EditWindow.js', 0);

        $this->resource->addJs('/js/app/system/Menu.js', 3);
        $this->resource->addJs('/js/app/system/crud/menu.js', 4);
    }

    public function initListeners()
    {
        $this->eventManager->on(EventManager::AFTER_LOAD, [$this, 'prepareData']);
        $this->eventManager->on(EventManager::AFTER_UPDATE_BEFORE_COMMIT, [$this, 'afterUpdate']);
        $this->eventManager->on(EventManager::AFTER_INSERT_BEFORE_COMMIT, [$this, 'afterInsert']);
    }

    /**
     * @param Event $event
     * @return void
     */
    public function prepareData(Event $event) : void
    {
        $data = &$event->getData()->data;

        if(empty($data)){
            return;
        }

        $objectConfig = Record\Config::factory($this->getObjectName());

        /*
         * Prepare  multi link properties
         */
        $fields = $objectConfig->getFields();

        foreach ($fields as $field) {

            $fieldName = $field->getName();

            if ($fieldName== 'id' || empty($data[$fieldName])) {
                continue;
            }

            if ($field->isObjectLink() || $field->isMultiLink()) {
                $linkObject = $field->getLinkedObject();
                $data[$fieldName] = array_values($this->collectLinksData($data[$fieldName], $linkObject));
            }
        }

        /**
         * @var \Model_Menu_Item $menuItemModel
         */
        $menuItemModel = Model::factory('menu_item');
        $data['data'] = $menuItemModel->getTreeList($data['id']);
    }

    /**
     * Get page list for combobox
     */
    public function pageListAction()
    {
        $pagesModel = Model::factory('Page');
        $data = $pagesModel->query()->fields(['id', 'title' => 'page_title'])->fetchAll();
        $this->response->success($data);
    }

    /**
     * @param Event $event
     * @return void
     */
    public function afterInsert(Event $event) : void
    {
        $eventData = $event->getData();

        if(!isset($eventData->object) || !$eventData->object instanceof RecordInterface){
            $event->setError($this->lang->get('CANT_EXEC'));
            return;
        }

        /**
         * @var RecordInterface $object
         */
        $object = $eventData->object;

        $linksData =  $this->request->post('data', 'raw', false);

        if (strlen($linksData)) {
            $linksData = json_decode($linksData, true);
        } else {
            $linksData = [];
        }

        /**
         * @var \Model_Menu_Item $menuModel
         */
        $menuModel = Model::factory('menu_item');
        if (!$menuModel->updateLinks($object->getId(), $linksData)) {
            $event->setError($this->lang->get('CANT_CREATE'));
            return;
        }
    }

    /**
     * @param Event $event
     * @return void
     */
    public function afterUpdate(Event $event) : void
    {
        $eventData = $event->getData();

        if(!isset($eventData->object) || !$eventData->object instanceof RecordInterface){
            $event->setError($this->lang->get('CANT_EXEC'));
            return;
        }

        /**
         * @var RecordInterface $object
         */
        $object = $eventData->object;
        $linksData =  $this->request->post('data', 'raw', false);

        if (strlen($linksData)) {
            $linksData = json_decode($linksData, true);
        } else {
            $linksData = [];
        }

        /**
         * @var \Model_Menu_Item $menuModel
         */
        $menuModel = Model::factory('Menu_Item');
        if (!$menuModel->updateLinks($object->getId(), $linksData)) {
            $event->setError($this->lang->get('CANT_CREATE'));
            return;
        }
    }

    /**
     * Import Site structure
     */
    public function importAction() : void
    {
        /**
         * @var \Model_Menu_Item $menuModel
         */
        $menuModel = Model::factory('Menu_Item');
        $this->response->success($menuModel->exportSiteStructure());
    }


    /**
     * Get desktop module info
     */
    public function desktopModuleInfo()
    {
        $projectData = [];
        $projectData['includes']['js'][] = '/js/app/system/Menu.js';
        $module = $this->getModule();
        /*
         * Module bootstrap
         */
        if (file_exists($this->appConfig->get('jsPath') . 'app/system/desktop/' . strtolower($module) . '.js')) {
            $projectData['includes']['js'][] = '/js/app/system/desktop/' . strtolower($module) . '.js';
        }

        return $projectData;
    }
}