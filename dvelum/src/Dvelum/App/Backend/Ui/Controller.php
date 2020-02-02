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

namespace Dvelum\App\Backend\Ui;

use Dvelum\App\Backend;
use Dvelum\Orm;
use Dvelum\Config;

abstract class Controller extends Backend\Api\Controller
{
    /**
     * Additional fields for VC Records UI
     * @var array
     */
    protected $revControlFields = [
        'user' => 'author_id',
        'updater' => 'editor_id'
    ];

    public function indexAction()
    {
        parent::indexAction();

        $objectName = $this->getObjectName();
        $moduleName = $this->getModule();
        $objectConfig = Orm\Record\Config::factory($objectName);
        $moduleAcl = $this->user->getModuleAcl();
        $modulesConfig = Config::factory(Config\Factory::File_Array, $this->appConfig->get('backend_modules'));
        $moduleCfg = $modulesConfig->get($moduleName);

        $this->includeScripts();

        $this->resource->addInlineJs(
            PHP_EOL . ' var canEdit = ' . intval($moduleAcl->canEdit($moduleName)) . ';' .
            PHP_EOL . ' var canDelete = ' . intval($moduleAcl->canDelete($moduleName)) . ';'
        );

        if ($objectConfig->isRevControl()) {
            $this->resource->addInlineJs(PHP_EOL . ' var canPublish =  ' . intval($moduleAcl->canPublish($moduleName)) . ';');
            $this->resource->addJs('/js/app/system/ContentWindow.js', 1);
            $this->resource->addJs('/js/app/system/RevisionPanel.js', 2);
        }

        if (strlen($moduleCfg['designer'])) {
            $this->runDesignerProject($moduleCfg['designer']);
        } else {
            if (file_exists($this->appConfig->get('jsPath') . 'app/system/crud/' . strtolower($moduleName) . '.js')) {
                $this->resource->addJs('/js/app/system/crud/' . strtolower($moduleName) . '.js', 4);
            }
        }
    }

    public function listAction()
    {
        $objectConfig = Orm\Record\Config::factory($this->getObjectName());
        // Add additional fields for VC Records UI
        if ($objectConfig->isRevControl()) {
            $this->listLinks = array_merge($this->listLinks, $this->revControlFields);
        }

        parent::listAction();
    }
}